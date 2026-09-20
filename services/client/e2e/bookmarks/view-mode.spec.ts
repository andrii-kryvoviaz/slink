import { expect, test } from '../fixtures/auth.fixture';
import { captureListingRequests } from '../helpers/listingRequests';
import { ssrViewModeChecked, stripSsrComments } from '../helpers/ssr';
import { BookmarksPage } from '../pages/BookmarksPage';

const BOOKMARKS_ENDPOINT = '/api/bookmarks';

test.describe('Bookmarks view mode', () => {
  test('keeps its heading, subtitle and body, with the view-mode toggle as its only header control', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageIdA = await owner.content.uploadImage({ isPublic: true });
    const imageIdB = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageIdA);
    await api.content.bookmarkImage(imageIdB);

    await bookmarksPage.goto();

    await expect(
      page.getByRole('heading', { name: 'Bookmarks', exact: true }),
    ).toBeVisible();
    await expect(
      page.getByText('Your saved images from the community', { exact: true }),
    ).toBeVisible();

    await expect(bookmarksPage.cardFor(imageIdA)).toBeVisible();
    await expect(bookmarksPage.cardFor(imageIdB)).toBeVisible();

    await expect(
      page.getByRole('radiogroup', { name: 'View mode' }),
    ).toHaveCount(1);
  });

  test('offers grid and list only, and switching repaints the listing', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    await bookmarksPage.goto();
    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();

    const radios = page
      .getByRole('radiogroup', { name: 'View mode' })
      .getByRole('radio');
    await expect(radios).toHaveCount(2);
    await expect(bookmarksPage.viewModeOption('Grid')).toHaveCount(1);
    await expect(bookmarksPage.viewModeOption('List')).toHaveCount(1);
    await expect(bookmarksPage.viewModeOption('Table')).toHaveCount(0);
    await expect(bookmarksPage.listRows).toHaveCount(0);

    await bookmarksPage.switchViewMode('List');

    await expect(bookmarksPage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(bookmarksPage.rowFor(imageId)).toHaveCount(1);

    await bookmarksPage.switchViewMode('Grid');

    await expect(bookmarksPage.listRows).toHaveCount(0);
    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();
  });

  test('writes the chosen mode to the settings cookie and repaints it after a reload', async ({
    page,
    bookmarksPage,
    layoutControls,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    await bookmarksPage.goto();
    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();

    await bookmarksPage.switchViewMode('List');

    await expect
      .poll(() => layoutControls.readSettingCookie('bookmarks'))
      .toBe(JSON.stringify({ viewMode: 'list' }));

    await page.reload();

    await expect(bookmarksPage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(bookmarksPage.rowFor(imageId)).toHaveCount(1);

    await layoutControls.setSettingCookie(
      'bookmarks',
      JSON.stringify({ viewMode: 'list' }),
    );
    await bookmarksPage.goto();

    await expect(bookmarksPage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(bookmarksPage.rowFor(imageId)).toHaveCount(1);
  });

  test('falls back to grid server-side when the settings cookie holds an unsupported mode', async ({
    page,
    bookmarksPage,
    layoutControls,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    await layoutControls.setSettingCookie(
      'bookmarks',
      JSON.stringify({ viewMode: 'table' }),
    );

    const html = await (await page.request.get(BookmarksPage.URL)).text();
    const stripped = stripSsrComments(html);

    expect(ssrViewModeChecked(stripped, 'Grid')).toBe(true);
    expect(ssrViewModeChecked(stripped, 'List')).toBe(false);
    expect(stripped).not.toContain('role="listbox"');
    expect(stripped).not.toContain('aria-haspopup="listbox"');

    await bookmarksPage.goto();

    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();
    await expect(bookmarksPage.viewModeOption('Grid')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(bookmarksPage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'false',
    );
    await expect(bookmarksPage.strayViewModeListbox).toHaveCount(0);

    await bookmarksPage.switchViewMode('List');

    await expect
      .poll(() => layoutControls.readSettingCookie('bookmarks'))
      .toBe(JSON.stringify({ viewMode: 'list' }));
  });

  test('loads the listing exactly once on first paint', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    const listings = captureListingRequests(page, BOOKMARKS_ENDPOINT);

    await bookmarksPage.goto();
    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();
    await page.waitForLoadState('networkidle');

    expect(listings).toHaveLength(1);
  });

  test('renders no page-size toolbar above the listing in either mode', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    const pageSizeControl = page
      .locator('main')
      .getByRole('button', { name: /per page|Show \d+|Items per page/i });

    await bookmarksPage.goto();
    await expect(bookmarksPage.cardFor(imageId)).toBeVisible();
    await expect(bookmarksPage.strayViewModeListbox).toHaveCount(0);
    await expect(pageSizeControl).toHaveCount(0);

    await bookmarksPage.switchViewMode('List');

    await expect(bookmarksPage.rowFor(imageId)).toHaveCount(1);
    await expect(bookmarksPage.strayViewModeListbox).toHaveCount(0);
    await expect(pageSizeControl).toHaveCount(0);
  });
});
