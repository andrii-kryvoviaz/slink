import { expect, test } from '../fixtures/auth.fixture';

test.describe('History batch delete', () => {
  test('selects multiple images and removes them from the feed', async ({
    page,
    historyPage,
    api,
  }) => {
    await api.content.uploadImage();
    await api.content.uploadImage();
    await api.content.uploadImage();

    await historyPage.useGridView();
    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });

    const before = await historyPage.gridCards.count();
    const selectCount = 2;
    const total = (await api.content.listHistoryIds(1000)).length;
    const expected = Math.min(total - selectCount, before);

    await historyPage.selectImages(selectCount);
    await expect(historyPage.deleteButton).toBeVisible();

    await historyPage.batchDelete(selectCount);

    await expect.poll(() => historyPage.gridCards.count()).toBe(expected);
  });
});
