import { expect, test } from '../fixtures/auth.fixture';
import { ApiClient } from '../helpers/api';
import { ensureUser } from '../helpers/slink';

const OTHER_USER = {
  email: 'bookmark-owner@test.local',
  username: 'bookmark-owner',
  password: 'Test123!',
};

async function uploadOtherUserPublicImage(): Promise<string> {
  ensureUser({
    email: OTHER_USER.email,
    username: OTHER_USER.username,
    password: OTHER_USER.password,
    active: true,
  });

  const client = await ApiClient.createForUser(
    OTHER_USER.username,
    OTHER_USER.password,
  );
  return client.content.uploadImage({ isPublic: true });
}

test.use({ storageState: 'e2e/.auth/user.json' });

test.describe('Explore bookmark', () => {
  test('saves and removes a bookmark from the viewer', async ({
    explorePage,
  }) => {
    const imageId = await uploadOtherUserPublicImage();

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

  test('bookmarked image appears in bookmarks page', async ({
    explorePage,
    page,
  }) => {
    const imageId = await uploadOtherUserPublicImage();

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

    const bookmarkLink = page
      .locator(`main a[href*="post=${imageId}"]`)
      .first();
    await expect(async () => {
      await page.goto('/bookmarks');
      await expect(bookmarkLink).toBeVisible({ timeout: 2000 });
    }).toPass({ timeout: 15000 });
  });
});
