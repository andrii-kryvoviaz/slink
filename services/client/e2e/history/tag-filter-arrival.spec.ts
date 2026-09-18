import { expect, test } from '../fixtures/auth.fixture';
import { captureListingRequests } from '../helpers/listingRequests';

test.describe('History tag filter arrival', () => {
  test('a tag filter arrival loads the history feed exactly once', async ({
    page,
    historyPage,
    api,
  }) => {
    const taggedId = await api.content.uploadImage();
    const untaggedId = await api.content.uploadImage();
    const tagId = await api.content.createTag(`arrivaltag${Date.now()}`);
    await api.content.tagImage(taggedId, tagId);

    await historyPage.useGridView();

    const listings = captureListingRequests(page, '/api/images/history');
    await page.goto(`/history?tagIds=${tagId}`);

    await expect.poll(() => listings.length).toBe(1);
    await expect(historyPage.cardFor(taggedId)).toBeVisible();
    await expect(historyPage.cardFor(untaggedId)).toHaveCount(0);

    expect(listings[0]).toContain('tagIds');
    expect(listings[0]).toContain(tagId);
    expect(listings, JSON.stringify(listings)).toHaveLength(1);
  });
});
