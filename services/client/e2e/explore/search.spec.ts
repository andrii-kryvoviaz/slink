import { expect, test } from '../fixtures/auth.fixture';
import { captureListingRequests } from '../helpers/listingRequests';

const HOLD_WINDOW_MS = 5000;

test.describe('Explore search', () => {
  test('filters the feed by the search term', async ({ explorePage, api }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.search('zzznonexistentqueryzzz');

    await expect(explorePage.feedItems).toHaveCount(0);
  });

  test('shows the empty state for a term that matches nothing', async ({
    page,
    explorePage,
  }) => {
    await explorePage.goto();
    await explorePage.search('zzznonexistentqueryzzz');

    await expect(explorePage.feedItems).toHaveCount(0);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
  });

  test('a search arrival loads the feed exactly once', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore?search=zzznonexistentqueryzzz&searchBy=user');

    await expect.poll(() => listings.length).toBe(1);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
    await expect(explorePage.searchInput).toHaveValue('zzznonexistentqueryzzz');

    expect(listings).toHaveLength(1);
    expect(listings[0]).toContain('searchTerm=zzznonexistentqueryzzz');
    expect(listings[0]).toContain('searchBy=user');
  });

  test('a plain arrival loads the feed exactly once', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore');

    await expect.poll(() => listings.length).toBe(1);
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    expect(listings).toHaveLength(1);
    expect(listings[0]).not.toContain('searchTerm=');
  });

  test('a blank search in the url behaves like no search', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    for (const blank of ['%20%20', '']) {
      const listings = captureListingRequests(page);
      await page.goto(`/explore?search=${blank}&searchBy=user`);

      await expect.poll(() => listings.length).toBe(1);
      await explorePage.feedItems.first().waitFor({ state: 'visible' });
      await expect(explorePage.searchInput).toHaveValue('');
      await expect(
        page.getByRole('heading', { name: 'No images found' }),
      ).toHaveCount(0);
      await expect(
        page.getByRole('heading', { name: 'Nothing shared yet' }),
      ).toHaveCount(0);

      const params = new URL(page.url()).searchParams;
      expect(params.get('search'), `search for search=${blank}`).toBeNull();
      expect(params.get('searchBy'), `searchBy for search=${blank}`).toBeNull();

      expect(
        listings,
        `search=${blank}: ${JSON.stringify(listings)}`,
      ).toHaveLength(1);
      expect(listings[0]).not.toContain('searchTerm=');
    }
  });

  test('a search without a scope in the url loads once and gains the default scope', async ({
    page,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore?search=zzznonexistentqueryzzz');

    await expect.poll(() => listings.length).toBe(1);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
    await expect
      .poll(() => new URL(page.url()).searchParams.get('searchBy'))
      .toBe('user');

    const params = new URL(page.url()).searchParams;
    expect(params.get('search')).toBe('zzznonexistentqueryzzz');
    expect(params.get('searchBy')).toBe('user');

    expect(listings).toHaveLength(1);
  });

  test('clearing a search from the url empties the field and the params and does not refilter', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await page.goto('/explore?search=zzznonexistentqueryzzz&searchBy=user');
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();

    const listings = captureListingRequests(page);
    await page.getByRole('button', { name: 'Clear', exact: true }).click();

    await expect.poll(() => listings.length).toBe(1);
    await expect(explorePage.feedItems.first()).toBeVisible();
    await expect(explorePage.searchInput).toHaveValue('');
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toHaveCount(0);

    const params = new URL(page.url()).searchParams;
    expect(params.get('search')).toBeNull();
    expect(params.get('searchBy')).toBeNull();

    const clearedAt = Date.now();
    await expect
      .poll(
        () =>
          Date.now() - clearedAt >= HOLD_WINDOW_MS ? 'elapsed' : 'waiting',
        { timeout: HOLD_WINDOW_MS + 2000 },
      )
      .toBe('elapsed');

    await expect(explorePage.feedItems.first()).toBeVisible();
    await expect(explorePage.searchInput).toHaveValue('');
    expect(
      listings.filter((url) => url.includes('searchTerm=')),
      `late refiltering requests: ${JSON.stringify(listings)}`,
    ).toHaveLength(0);
    expect(listings, JSON.stringify(listings)).toHaveLength(1);
  });

  test('a typed term writes the search into the url', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.searchInput.fill('zzznonexistentqueryzzz');

    await expect(async () => {
      const params = new URL(page.url()).searchParams;
      expect(params.get('search')).toBe('zzznonexistentqueryzzz');
      expect(params.get('searchBy')).toBe('user');
    }).toPass({ timeout: 15000 });

    await expect(explorePage.feedItems).toHaveCount(0);
    await expect(explorePage.searchInput).toBeFocused();
  });
});
