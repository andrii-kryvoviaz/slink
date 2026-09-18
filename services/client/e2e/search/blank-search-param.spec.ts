import { expect, test } from '../fixtures/auth.fixture';

const BLANK_SEARCH_VALUES = ['', '%20%20'];
const VISIBLE_TIMEOUT = 15000;

test.describe('Blank search param', () => {
  test('a blank search param on /history still lists the seeded upload', async ({
    page,
    historyPage,
    api,
  }) => {
    const imageId = await api.content.uploadImage();

    for (const blank of BLANK_SEARCH_VALUES) {
      await page.goto(`/history?search=${blank}`);

      await expect(historyPage.infoLink(imageId)).toBeVisible({
        timeout: VISIBLE_TIMEOUT,
      });
      await expect(
        page.getByRole('heading', { name: 'No uploads yet' }),
      ).toHaveCount(0);
    }
  });

  test('a blank search param on /collections still lists the seeded collection', async ({
    page,
    collectionsPage,
    api,
  }) => {
    const name = `E2E Blank Search ${Date.now()}`;
    await api.content.createCollection({ name });

    for (const blank of BLANK_SEARCH_VALUES) {
      await page.goto(`/collections?search=${blank}`);

      await expect(page.getByText(name, { exact: true })).toBeVisible({
        timeout: VISIBLE_TIMEOUT,
      });
      await expect(
        page.getByRole('heading', { name: 'No collections yet' }),
      ).toHaveCount(0);
    }
  });

  test('a blank search param on /tags still lists the seeded tag', async ({
    page,
    api,
  }) => {
    const name = `e2etag${Date.now()}`;
    await api.content.createTag(name);

    for (const blank of BLANK_SEARCH_VALUES) {
      await page.goto(`/tags?search=${blank}`);

      await expect(page.getByRole('row', { name })).toBeVisible({
        timeout: VISIBLE_TIMEOUT,
      });
      await expect(
        page.getByRole('heading', { name: 'No tags yet' }),
      ).toHaveCount(0);
      await expect(
        page.getByRole('heading', { name: 'No tags found' }),
      ).toHaveCount(0);
    }
  });
});
