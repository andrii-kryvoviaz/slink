import { expect, test } from '../fixtures/auth.fixture';

const SEARCH_TERM = 'zzznonexistentqueryzzz';
const HASHTAG_TERM = '#zzznonexistenttagzzz';

function matchesListingRequest(url: URL, searchTerm: string, searchBy: string) {
  return (
    url.pathname === '/api/images' &&
    url.searchParams.get('searchTerm') === searchTerm &&
    url.searchParams.get('searchBy') === searchBy
  );
}

test.describe('Explore search scope', () => {
  test('selecting Description searches by description', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.search(SEARCH_TERM);

    const requestPromise = page.waitForRequest(
      (request) =>
        request.method() === 'GET' &&
        matchesListingRequest(
          new URL(request.url()),
          SEARCH_TERM,
          'description',
        ),
    );
    await explorePage.selectSearchBy('Description');
    await requestPromise;

    await expect
      .poll(() => new URL(page.url()).searchParams.get('searchBy'))
      .toBe('description');
    expect(new URL(page.url()).searchParams.get('search')).toBe(SEARCH_TERM);
  });

  test('selecting Hashtag searches by hashtag', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.search(SEARCH_TERM);

    const requestPromise = page.waitForRequest(
      (request) =>
        request.method() === 'GET' &&
        matchesListingRequest(new URL(request.url()), SEARCH_TERM, 'hashtag'),
    );
    await explorePage.selectSearchBy('Hashtag');
    await requestPromise;

    await expect
      .poll(() => new URL(page.url()).searchParams.get('searchBy'))
      .toBe('hashtag');
    expect(new URL(page.url()).searchParams.get('search')).toBe(SEARCH_TERM);
  });

  test('a hashtag term switches the scope to hashtag on its own', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await expect(explorePage.searchOptionsTrigger).toContainText('User');

    const requestPromise = page.waitForRequest(
      (request) =>
        request.method() === 'GET' &&
        matchesListingRequest(new URL(request.url()), HASHTAG_TERM, 'hashtag'),
    );
    await explorePage.search(HASHTAG_TERM);
    await requestPromise;

    await expect
      .poll(() => new URL(page.url()).searchParams.get('searchBy'))
      .toBe('hashtag');
    expect(new URL(page.url()).searchParams.get('search')).toBe(HASHTAG_TERM);

    await expect(explorePage.searchOptionsTrigger).toContainText('Hashtag');
    await expect(page.getByRole('menuitem')).toHaveCount(0);
    await expect(page.getByPlaceholder(/Search hashtags/)).toHaveValue(
      HASHTAG_TERM,
    );
  });

  test('the scope menu lists User, Description and Hashtag in that order', async ({
    page,
    explorePage,
  }) => {
    await explorePage.goto();

    await expect(async () => {
      await explorePage.searchOptionsTrigger.click();
      await expect(page.getByRole('menuitem').first()).toBeVisible({
        timeout: 1000,
      });
    }).toPass();

    await expect(page.getByRole('menuitem')).toHaveText([
      'Search by User',
      'Search by Description',
      'Search by Hashtag',
    ]);

    await page.keyboard.press('Escape');
    await expect(page.getByRole('menuitem')).toHaveCount(0);
  });
});
