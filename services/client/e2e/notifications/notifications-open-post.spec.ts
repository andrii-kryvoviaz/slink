import type { Browser, Page, Response } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { ExplorePage } from '../pages/ExplorePage';
import { NotificationsPage } from '../pages/NotificationsPage';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const EXPLORE_PAGE_SIZE = 12;
const FILLER_COUNT = EXPLORE_PAGE_SIZE + 2;
const UNKNOWN_IMAGE_ID = '00000000-0000-0000-0000-000000000000';
const UNHANDLED_ERROR = /uncaught|unhandled/i;

interface BookmarkedImage {
  owner: Account;
  imageId: string;
}

async function hideImage(api: ApiClient, imageId: string): Promise<void> {
  const response = await fetch(`${API_URL}/api/image/${imageId}`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${api.token}`,
    },
    body: JSON.stringify({ description: null, isPublic: false }),
  });

  expect(response.ok).toBe(true);
}

async function seedBookmarkedImage(): Promise<{
  owner: Account;
  ownerApi: ApiClient;
  imageId: string;
}> {
  const owner = unique('notif-owner');
  const ownerApi = await provisionUser(owner);
  const readerApi = await provisionUser(unique('notif-reader'));

  const imageId = await ownerApi.content.uploadImage({ isPublic: true });
  await readerApi.content.bookmarkImage(imageId);

  return { owner, ownerApi, imageId };
}

async function seedOwnedNonPublicImage(): Promise<BookmarkedImage> {
  const { owner, ownerApi, imageId } = await seedBookmarkedImage();
  await hideImage(ownerApi, imageId);

  return { owner, imageId };
}

async function seedPublicImageOffFirstPage(): Promise<BookmarkedImage> {
  const { owner, imageId } = await seedBookmarkedImage();
  const fillerApi = await provisionUser(unique('notif-filler'));

  for (let i = 0; i < FILLER_COUNT; i++) {
    await fillerApi.content.uploadImage({ isPublic: true });
  }

  return { owner, imageId };
}

function isPublicImageRequest(response: Response, imageId: string): boolean {
  return new URL(response.url()).pathname.endsWith(`/image/${imageId}/public`);
}

async function withSignedInPage(
  browser: Browser,
  account: Account,
  run: (page: Page) => Promise<void>,
) {
  const context = await signInContext(browser, account);

  try {
    await run(await context.newPage());
  } finally {
    await context.close();
  }
}

function collectPageErrors(page: Page): string[] {
  const errors: string[] = [];
  page.on('pageerror', (error) => errors.push(error.message));
  page.on('console', (message) => {
    if (message.type() === 'error' && UNHANDLED_ERROR.test(message.text())) {
      errors.push(message.text());
    }
  });

  return errors;
}

async function openFromBookmarkNotification(page: Page) {
  const notificationsPage = new NotificationsPage(page);
  await notificationsPage.goto();

  const entry = notificationsPage.entryByText(/bookmarked/);
  await expect(entry).toBeVisible();
  await notificationsPage.entrySentence(entry).click();
  await expect(page).toHaveURL(/\/notifications$/);
  await notificationsPage.openPostButton(entry).click();
}

async function expectViewerShows(page: Page, imageId: string) {
  const explorePage = new ExplorePage(page);

  await expect(page).toHaveURL(new RegExp(`/explore\\?post=${imageId}`));
  await expect(explorePage.viewer).toBeVisible();
  await expect(
    explorePage.viewer.locator(`img[src*="${imageId}"]`).first(),
  ).toBeVisible();
  await expect(page).toHaveURL(new RegExp(`post=${imageId}`));
}

async function expectCleanExploreFallback(page: Page, errors: string[]) {
  const explorePage = new ExplorePage(page);

  await expect
    .poll(() => {
      const url = new URL(page.url());
      return (
        url.pathname === ExplorePage.URL &&
        !url.searchParams.has('post') &&
        !url.searchParams.has('comment')
      );
    })
    .toBe(true);
  await expect(explorePage.viewer).toHaveCount(0);
  await expect(explorePage.searchInput.first()).toBeVisible();
  expect(errors).toEqual([]);
}

test.describe('Notifications open post', () => {
  test("falls back to explore when the owner's non-public image is opened from a notification", async ({
    browser,
  }) => {
    const { owner, imageId } = await seedOwnedNonPublicImage();

    await withSignedInPage(browser, owner, async (page) => {
      const errors = collectPageErrors(page);
      const fetched = page.waitForResponse((response) =>
        isPublicImageRequest(response, imageId),
      );

      await openFromBookmarkNotification(page);

      expect((await fetched).status()).toBe(404);
      await expectCleanExploreFallback(page, errors);
    });
  });

  test('opens a public image outside the first explore page', async ({
    browser,
  }) => {
    const { owner, imageId } = await seedPublicImageOffFirstPage();

    await withSignedInPage(browser, owner, async (page) => {
      const explorePage = new ExplorePage(page);
      await explorePage.goto();
      await expect(explorePage.feedItems.first()).toBeVisible();
      await expect(explorePage.cardFor(imageId)).toHaveCount(0);

      await openFromBookmarkNotification(page);

      await expectViewerShows(page, imageId);
    });
  });

  test('reopens a standalone post from the notification after closing it', async ({
    browser,
  }) => {
    const { owner, imageId } = await seedPublicImageOffFirstPage();

    await withSignedInPage(browser, owner, async (page) => {
      const explorePage = new ExplorePage(page);

      await openFromBookmarkNotification(page);
      await expectViewerShows(page, imageId);

      await explorePage.closeViewer();
      await expect
        .poll(() => new URL(page.url()).searchParams.has('post'))
        .toBe(false);

      await openFromBookmarkNotification(page);
      await expectViewerShows(page, imageId);
    });
  });

  test('clears the url without errors when the post cannot be opened', async ({
    browser,
  }) => {
    const { imageId } = await seedOwnedNonPublicImage();
    const nonOwner = unique('notif-other');
    await provisionUser(nonOwner);

    await withSignedInPage(browser, nonOwner, async (page) => {
      const errors = collectPageErrors(page);
      const fetched = page.waitForResponse((response) =>
        isPublicImageRequest(response, imageId),
      );

      await page.goto(`/explore?post=${imageId}&comment=abc`);

      expect((await fetched).status()).toBe(404);
      await expectCleanExploreFallback(page, errors);
    });
  });

  test('clears the url without errors when the post does not exist', async ({
    browser,
  }) => {
    const account = unique('notif-unknown');
    await provisionUser(account);

    await withSignedInPage(browser, account, async (page) => {
      const errors = collectPageErrors(page);
      const fetched = page.waitForResponse((response) =>
        isPublicImageRequest(response, UNKNOWN_IMAGE_ID),
      );

      await page.goto(`/explore?post=${UNKNOWN_IMAGE_ID}&comment=x`);

      expect((await fetched).status()).toBe(404);
      await expectCleanExploreFallback(page, errors);
    });
  });
});
