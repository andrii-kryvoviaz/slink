import { execFileSync } from 'child_process';

import { expect, test } from '../fixtures/auth.fixture';
import { ContentApiClient } from '../helpers/ContentApiClient';

const DOCKER_ARGS = [
  'compose',
  '-p',
  'slink-e2e',
  'exec',
  '-T',
  'slink',
  'slink',
];

function slink(...args: string[]) {
  execFileSync('docker', [...DOCKER_ARGS, ...args]);
}

const OTHER_USER = {
  email: 'bookmark-owner@test.local',
  username: 'bookmark-owner',
  password: 'Test123!',
};

async function uploadOtherUserPublicImage(): Promise<string> {
  try {
    slink(
      'user:create',
      `--email=${OTHER_USER.email}`,
      `--username=${OTHER_USER.username}`,
      '-p',
      OTHER_USER.password,
      '-a',
    );
  } catch {
    try {
      slink('user:activate', `--email=${OTHER_USER.email}`);
    } catch {}
  }

  const client = await ContentApiClient.createForUser(
    OTHER_USER.username,
    OTHER_USER.password,
  );
  return client.uploadImage({ isPublic: true });
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
    await explorePage.toggleBookmark(bookmarkButton, 'true');

    await page.goto('/bookmarks');
    await expect(
      page.locator(`main a[href*="post=${imageId}"]`).first(),
    ).toBeVisible();
  });
});
