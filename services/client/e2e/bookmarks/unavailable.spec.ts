import { type Locator } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';

const BOOKMARKS_ENDPOINT = '/api/bookmarks';

const SAVED_AT = {
  formattedDate: '2021-06-15 10:00:00',
  timestamp: 1623751200,
};
const UPLOADED_AT = {
  formattedDate: '2013-02-03 10:00:00',
  timestamp: 1359885600,
};
const SAVED_YEAR = '2021';
const UPLOADED_YEAR = '2013';

const savedBadge = (scope: Locator) =>
  scope.getByRole('img', { name: /^Saved on / });

const settle = () => new Promise((resolve) => setTimeout(resolve, 1100));

test.describe('Bookmarks saved date', () => {
  test('shows the bookmark date in the badge while the card body keeps the image date', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(imageId);

    await page.route(
      (url) => new URL(url).pathname === BOOKMARKS_ENDPOINT,
      async (route) => {
        const response = await route.fetch();
        const body = await response.json();
        const items = body.data ?? body;

        for (const item of items) {
          if (item.image?.id !== imageId) continue;
          item.createdAt = { ...SAVED_AT };
          if (item.image.attributes) {
            item.image.attributes.createdAt = { ...UPLOADED_AT };
          }
        }

        await route.fulfill({ response, json: body });
      },
    );

    await bookmarksPage.goto();

    const card = bookmarksPage.cardFor(imageId);
    await expect(card).toBeVisible();
    await expect(card).toContainText(SAVED_YEAR);
    await expect(card).toContainText(UPLOADED_YEAR);

    const cardBadge = savedBadge(card);
    await expect(cardBadge).toHaveCount(1);
    await expect(cardBadge).toHaveAttribute(
      'aria-label',
      new RegExp(`^Saved on .*${SAVED_YEAR}`),
    );
    await expect(cardBadge).toContainText(SAVED_YEAR);
    await expect(cardBadge).not.toContainText(UPLOADED_YEAR);

    await bookmarksPage.switchViewMode('List');

    const row = bookmarksPage.rowFor(imageId);
    await expect(row).toHaveCount(1);

    const rowBadge = savedBadge(row);
    await expect(rowBadge).toHaveCount(1);
    await expect(rowBadge).toHaveAttribute(
      'aria-label',
      new RegExp(`^Saved on .*${SAVED_YEAR}`),
    );
    await expect(rowBadge).toContainText(SAVED_YEAR);
  });
});

test.describe('Bookmarks unavailable placeholder', () => {
  test('replaces the grid card and removes the bookmark without a reload', async ({
    page,
    bookmarksPage,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const unavailableId = await owner.content.uploadImage({ isPublic: true });
    const availableId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(unavailableId);
    await api.content.bookmarkImage(availableId);
    await owner.content.setMediaVisibility([unavailableId], false);

    const pageErrors: string[] = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await bookmarksPage.goto();
    await expect(bookmarksPage.cardFor(availableId)).toBeVisible();

    await expect(bookmarksPage.unavailableItem()).toHaveCount(1);
    await expect(bookmarksPage.unavailableItem()).toContainText(
      bookmarksPage.unavailableText,
    );
    await expect(bookmarksPage.cardFor(unavailableId)).toHaveCount(0);
    await expect(bookmarksPage.mediaFor(unavailableId)).toHaveCount(0);
    await expect(bookmarksPage.removeUnavailableButton()).toHaveCount(1);

    let navigations = 0;
    page.on('framenavigated', (frame) => {
      if (frame === page.mainFrame()) navigations++;
    });

    const removal = page.waitForResponse(
      (response) =>
        response.request().method() === 'DELETE' &&
        new URL(response.url()).pathname ===
          `/api/image/${unavailableId}/bookmark`,
    );
    await bookmarksPage.removeUnavailableButton().click();

    expect((await removal).ok()).toBe(true);

    await expect(bookmarksPage.unavailableItem()).toHaveCount(0);
    await expect(bookmarksPage.cardFor(availableId)).toBeVisible();
    await expect(bookmarksPage.emptyState()).toHaveCount(0);
    expect(navigations).toBe(0);
    expect(pageErrors).toEqual([]);
  });

  test('sits inside a single list row and removes the bookmark without a reload', async ({
    page,
    bookmarksPage,
    layoutControls,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const unavailableId = await owner.content.uploadImage({ isPublic: true });
    const availableId = await owner.content.uploadImage({ isPublic: true });
    await api.content.bookmarkImage(unavailableId);
    await api.content.bookmarkImage(availableId);
    await owner.content.setMediaVisibility([unavailableId], false);

    await layoutControls.setSettingCookie(
      'bookmarks',
      JSON.stringify({ viewMode: 'list' }),
    );

    await bookmarksPage.goto();
    await expect(bookmarksPage.rowFor(availableId)).toHaveCount(1);

    await expect(bookmarksPage.unavailableItem()).toHaveCount(1);
    await expect(bookmarksPage.unavailableRow()).toHaveCount(1);
    await expect(bookmarksPage.unavailableRow().locator('li')).toHaveCount(0);
    await expect(bookmarksPage.removeUnavailableButton()).toHaveCount(1);

    let navigations = 0;
    page.on('framenavigated', (frame) => {
      if (frame === page.mainFrame()) navigations++;
    });

    const removal = page.waitForResponse(
      (response) =>
        response.request().method() === 'DELETE' &&
        new URL(response.url()).pathname ===
          `/api/image/${unavailableId}/bookmark`,
    );
    await bookmarksPage.removeUnavailableButton().click();

    expect((await removal).ok()).toBe(true);

    await expect(bookmarksPage.unavailableItem()).toHaveCount(0);
    await expect(bookmarksPage.unavailableRow()).toHaveCount(0);
    await expect(bookmarksPage.rowFor(availableId)).toHaveCount(1);
    expect(navigations).toBe(0);
  });

  test('opens the viewer on the clicked item when an unavailable bookmark sits above it', async ({
    bookmarksPage,
    layoutControls,
    api,
    actor,
  }) => {
    const owner = await actor('owner');
    const firstId = await owner.content.uploadImage({ isPublic: true });
    const secondId = await owner.content.uploadImage({ isPublic: true });
    const unavailableId = await owner.content.uploadImage({ isPublic: true });

    await api.content.bookmarkImage(firstId);
    await settle();
    await api.content.bookmarkImage(secondId);
    await settle();
    await api.content.bookmarkImage(unavailableId);
    await owner.content.setMediaVisibility([unavailableId], false);

    await layoutControls.setSettingCookie(
      'bookmarks',
      JSON.stringify({ viewMode: 'list' }),
    );

    await bookmarksPage.goto();
    await expect(bookmarksPage.rowFor(secondId)).toHaveCount(1);
    await expect(bookmarksPage.listRows.first()).toContainText(
      bookmarksPage.unavailableText,
    );

    await bookmarksPage.clickUntil(
      bookmarksPage.rowFor(secondId),
      bookmarksPage.viewer,
    );
    expect(bookmarksPage.currentPost()).toBe(secondId);

    await bookmarksPage.nextItem();
    expect(bookmarksPage.currentPost()).toBe(firstId);

    await bookmarksPage.prevItem();
    expect(bookmarksPage.currentPost()).toBe(secondId);

    await bookmarksPage.closeViewer();
    await owner.content.setMediaVisibility([unavailableId], true);
  });
});
