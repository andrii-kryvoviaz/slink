import { expect, test } from '../fixtures/auth.fixture';

test.describe('Bookmarks page removal', () => {
  test('removes a bookmark from the bookmarks page card', async ({
    explorePage,
    actor,
    page,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const bookmarkButton = explorePage.viewer
      .locator(`[data-post-id="${imageId}"]`)
      .locator('button[aria-pressed]');

    const saved = page.waitForResponse(
      (response) =>
        response.request().method() === 'POST' &&
        new URL(response.url()).pathname.endsWith(
          `/image/${imageId}/bookmark`,
        ) &&
        response.ok(),
    );
    await explorePage.toggleBookmark(bookmarkButton, 'true');
    await saved;

    const card = page.locator(`main a[href*="post=${imageId}"]`).first();
    await expect(async () => {
      await page.goto('/bookmarks');
      await expect(card).toBeVisible({ timeout: 2000 });
    }).toPass({ timeout: 15000 });

    const removeButton = page
      .getByRole('button', { name: 'Remove bookmark' })
      .first();

    const removed = page.waitForResponse(
      (response) =>
        response.request().method() === 'DELETE' &&
        new URL(response.url()).pathname.endsWith(
          `/image/${imageId}/bookmark`,
        ) &&
        response.ok(),
    );
    await removeButton.click();
    await removed;

    await expect(card).toBeHidden();
  });
});
