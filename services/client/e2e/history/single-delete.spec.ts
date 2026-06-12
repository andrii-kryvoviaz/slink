import { expect, test } from '../fixtures/auth.fixture';

test.describe('History single delete', () => {
  test('removes one image from a card without affecting the others', async ({
    historyPage,
    api,
  }) => {
    const firstId = await api.content.uploadImage();
    const secondId = await api.content.uploadImage();

    await historyPage.useGridView();
    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });

    await expect(historyPage.infoLink(firstId)).toBeVisible();
    await expect(historyPage.infoLink(secondId)).toBeVisible();

    const before = await historyPage.gridCards.count();
    const total = (await api.content.listHistoryIds(1000)).length;
    const expected = Math.min(total - 1, before);

    await historyPage.deleteSingle(firstId);

    await expect(historyPage.infoLink(firstId)).toBeHidden();
    await expect(historyPage.infoLink(secondId)).toBeVisible();
    await expect.poll(() => historyPage.gridCards.count()).toBe(expected);
  });
});

test.describe('History view modes', () => {
  test('switches between list and table layouts', async ({
    historyPage,
    api,
  }) => {
    await api.content.uploadImage();
    await api.content.uploadImage();

    await historyPage.useGridView();
    await historyPage.goto();
    await historyPage.gridCards.first().waitFor({ state: 'visible' });

    await historyPage.switchViewMode('List');
    await expect(
      historyPage.page.getByRole('list').locator('a[href^="/info/"]').first(),
    ).toBeVisible();

    await historyPage.switchViewMode('Table');
    await expect(historyPage.page.getByRole('table')).toBeVisible();
    await expect(
      historyPage.page.getByRole('table').getByRole('row').nth(1),
    ).toBeVisible();
  });
});
