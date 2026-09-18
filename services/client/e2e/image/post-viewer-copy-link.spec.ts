import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Post viewer copy link', () => {
  test('lets the owner copy from the toolbar and persists the chosen format', async ({
    api,
    page,
    explorePage,
    layoutControls,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    const copyButton = item.getByRole('button', {
      name: 'Copy link',
      exact: true,
    });
    await expect(copyButton).toBeVisible();

    const caret = item.getByRole('button', { name: 'Copy link format' });
    await caret.click();

    const markdownItem = page.getByRole('menuitem', { name: 'Markdown' });
    await expect(markdownItem).toBeVisible();
    await markdownItem.click();

    await expect(item.getByRole('button', { name: 'Copied' })).toBeVisible();

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText.length).toBeGreaterThan(0);

    const shareCookie = await layoutControls.readSettingCookie('share');
    expect(shareCookie).not.toBeNull();
    expect(JSON.parse(shareCookie!)).toEqual({ format: 'markdown' });
  });

  test('copies the BBCode format wrapping the share link', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    const caret = item.getByRole('button', { name: 'Copy link format' });
    await caret.click();

    const bbcodeItem = page.getByRole('menuitem', { name: 'BBCode' });
    await expect(bbcodeItem).toBeVisible();
    await bbcodeItem.click();

    await expect(item.getByRole('button', { name: 'Copied' })).toBeVisible();

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText).toMatch(/^\[img\].+\[\/img\]$/);
  });

  test('copies the HTML format wrapping the share link with the file name as alt', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const { fileName } = await api.content.getImageDetail(imageId);

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    const caret = item.getByRole('button', { name: 'Copy link format' });
    await caret.click();

    const htmlItem = page.getByRole('menuitem', { name: 'HTML' });
    await expect(htmlItem).toBeVisible();
    await htmlItem.click();

    await expect(item.getByRole('button', { name: 'Copied' })).toBeVisible();

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText).toMatch(/^<img src=".+" alt=".+" \/>$/);
    expect(clipboardText).toContain(`alt="${fileName}"`);
  });

  test('owner copies the share link, which stays copied until the 1500ms reset', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    const copyButton = item.getByRole('button', {
      name: 'Copy link',
      exact: true,
    });
    const copiedButton = item.getByRole('button', { name: 'Copied' });

    await explorePage.gotoWithPausedClock(async () => {
      await explorePage.page.goto(`/explore?post=${imageId}`);
      await expect(copyButton).toBeEnabled();
    });

    await copyButton.click();
    await expect(copiedButton).toBeDisabled();

    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
      share.shareUrl,
    );

    await page.clock.runFor(1499);
    await expect(copiedButton).toBeDisabled();

    await page.clock.runFor(1);
    await expect(copyButton).toBeEnabled();
  });

  test('owner sees one error toast and no copied state when the clipboard write fails', async ({
    api,
    page,
    explorePage,
  }) => {
    await page.addInitScript(() => {
      Clipboard.prototype.writeText = () =>
        Promise.reject(new Error('Clipboard write denied'));
    });

    const imageId = await api.content.uploadImage({ isPublic: true });

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    const copyButton = item.getByRole('button', {
      name: 'Copy link',
      exact: true,
    });
    const errorToasts = page
      .locator('[data-sonner-toast]')
      .filter({ hasText: 'Something went wrong' });

    await explorePage.gotoWithPausedClock(async () => {
      await explorePage.page.goto(`/explore?post=${imageId}`);
      await expect(copyButton).toBeEnabled();
    });

    await copyButton.click();

    await expect(errorToasts).toHaveCount(1);
    await expect(copyButton).toBeEnabled();
    await expect(item.getByRole('button', { name: 'Copied' })).toHaveCount(0);
  });

  test('does not reopen the viewer when navigating away and back to explore', async ({
    api,
    page,
    explorePage,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const exploreLink = page.getByRole('link', { name: 'Explore' });

    await page.goto('/history');
    await exploreLink.click();
    await page.waitForURL(/\/explore/);

    await explorePage.openFirstItem();
    await expect(explorePage.viewer).toBeVisible();

    await page.goBack();
    await page.waitForURL(/\/history/);
    await expect(explorePage.viewer).toHaveCount(0);

    await exploreLink.click();
    await page.waitForURL(/\/explore/);
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await expect(explorePage.viewer).toHaveCount(0);
  });

  test('shows a bookmark control instead of copy link for a non-owner viewing a public image', async ({
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const item = explorePage.viewer.locator(`[data-post-id="${imageId}"]`);
    await expect(item.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(
      item.getByRole('button', { name: 'Copy link', exact: true }),
    ).toHaveCount(0);
  });
});
