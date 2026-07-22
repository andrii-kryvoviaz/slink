import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Post viewer copy link', () => {
  test('lets the owner copy from the toolbar and persists the chosen format', async ({
    api,
    page,
    explorePage,
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

    const shareCookie = (await page.context().cookies()).find(
      (cookie) => cookie.name === 'settings.share',
    );
    expect(shareCookie).toBeDefined();
    expect(JSON.parse(shareCookie!.value)).toEqual({ format: 'markdown' });
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
