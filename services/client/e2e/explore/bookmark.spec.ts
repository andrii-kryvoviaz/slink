import { expect, test } from '../fixtures/auth.fixture';

test.describe('Explore bookmark', () => {
  test('saves and removes a bookmark from the viewer', async ({
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const bookmarkButton = explorePage.viewer
      .locator(`[data-post-id="${imageId}"]`)
      .locator('button[aria-pressed]');
    await expect(bookmarkButton).toHaveAttribute('aria-pressed', 'false');

    await explorePage.toggleBookmark(bookmarkButton, 'true');
    await expect(bookmarkButton).toHaveAttribute(
      'aria-label',
      'Remove bookmark',
    );

    await explorePage.toggleBookmark(bookmarkButton, 'false');
    await expect(bookmarkButton).toHaveAttribute('aria-label', 'Save');
  });
});
