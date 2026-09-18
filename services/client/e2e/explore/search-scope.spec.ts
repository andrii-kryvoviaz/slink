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
  });
});
