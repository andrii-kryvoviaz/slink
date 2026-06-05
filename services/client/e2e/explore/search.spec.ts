import { expect, test } from '../fixtures/auth.fixture';

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
    await expect(page.getByText(/No images match your search/i)).toBeVisible();
  });
});
