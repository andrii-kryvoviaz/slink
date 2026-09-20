import type { Browser, BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { NotificationsPage } from '../pages/NotificationsPage';

const CHIPS = ['All', 'Unread', 'Comments', 'Replies', 'Bookmarks'] as const;

const UNHANDLED_ERROR = /uncaught|unhandled/i;

async function seedMixed(): Promise<Account> {
  const owner = unique('filter-owner');
  const ownerApi = await provisionUser(owner);
  const firstReaderApi = await provisionUser(unique('filter-first'));
  const secondReaderApi = await provisionUser(unique('filter-second'));
  const thirdReaderApi = await provisionUser(unique('filter-third'));

  const commentedImage = await ownerApi.content.uploadImage({ isPublic: true });
  const bookmarkedImage = await ownerApi.content.uploadImage({
    isPublic: true,
  });
  const repliedImage = await ownerApi.content.uploadImage({ isPublic: true });

  await firstReaderApi.content.bookmarkImage(bookmarkedImage);
  await secondReaderApi.content.createComment(
    commentedImage,
    'a plain comment',
  );
  const ownerNoteId = await ownerApi.content.createComment(
    repliedImage,
    'owner note',
  );
  await thirdReaderApi.content.createReply(
    repliedImage,
    ownerNoteId,
    'a reply to owner',
  );

  return owner;
}

async function withFiltersPage(
  browser: Browser,
  owner: Account,
  run: (notificationsPage: NotificationsPage, page: Page) => Promise<void>,
) {
  let context: BrowserContext | undefined;

  try {
    context = await signInContext(browser, owner);
    const page = await context.newPage();
    const notificationsPage = new NotificationsPage(page);
    const listed = notificationsPage.waitForList();
    await notificationsPage.goto();
    await listed;
    await run(notificationsPage, page);
  } finally {
    await context?.close();
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

async function expectOnlySelected(
  notificationsPage: NotificationsPage,
  selected: (typeof CHIPS)[number],
) {
  for (const label of CHIPS) {
    await expect(notificationsPage.filterChip(label)).toHaveAttribute(
      'aria-checked',
      String(label === selected),
    );
  }
}

function entries(notificationsPage: NotificationsPage) {
  return {
    bookmark: notificationsPage.entryByText(/bookmarked/),
    comment: notificationsPage.entryByText(/\bcommented\b/),
    reply: notificationsPage.entryByText('replied to your comment'),
  };
}

test.describe('Notification filters', () => {
  test('narrows the list per chip and restores everything under All', async ({
    browser,
  }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage, page) => {
      const errors = collectPageErrors(page);
      const failed: string[] = [];
      page.on('response', (response) => {
        const url = new URL(response.url());
        if (url.pathname.endsWith('/notifications') && !response.ok()) {
          failed.push(`${response.status()} ${url.search}`);
        }
      });
      const { bookmark, comment, reply } = entries(notificationsPage);

      await expect(notificationsPage.filterGroup).toBeVisible();
      await expect(notificationsPage.filterGroup.getByRole('radio')).toHaveText(
        [...CHIPS],
      );
      await expectOnlySelected(notificationsPage, 'All');
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');

      const bookmarkQuery = await notificationsPage.selectFilter('Bookmarks');
      expect(bookmarkQuery.get('type')).toBe('added_to_bookmarks');
      expect(bookmarkQuery.get('page')).toBe('1');
      expect(bookmarkQuery.has('unread')).toBe(false);
      await expectOnlySelected(notificationsPage, 'Bookmarks');
      await expect(bookmark).toHaveCount(1);
      await expect(comment).toHaveCount(0);
      await expect(reply).toHaveCount(0);

      const replyQuery = await notificationsPage.selectFilter('Replies');
      expect(replyQuery.get('type')).toBe('comment_reply');
      await expectOnlySelected(notificationsPage, 'Replies');
      await expect(reply).toHaveCount(1);
      await expect(bookmark).toHaveCount(0);
      await expect(comment).toHaveCount(0);

      const commentQuery = await notificationsPage.selectFilter('Comments');
      expect(commentQuery.get('type')).toBe('comment');
      await expect(comment).toHaveCount(1);
      await expect(comment.getByText('a plain comment')).toBeVisible();
      await expect(bookmark).toHaveCount(0);
      await expect(reply).toHaveCount(0);
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');

      const allQuery = await notificationsPage.selectFilter('All');
      expect(allQuery.has('type')).toBe(false);
      expect(allQuery.has('unread')).toBe(false);
      await expectOnlySelected(notificationsPage, 'All');
      await expect(bookmark).toHaveCount(1);
      await expect(comment).toHaveCount(1);
      await expect(reply).toHaveCount(1);
      await expect(page.getByText('Today', { exact: true })).toBeVisible();

      expect(errors).toEqual([]);
      expect(failed).toEqual([]);
    });
  });

  test('ignores a click on the already selected chip', async ({ browser }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage, page) => {
      await notificationsPage.selectFilter('Comments');
      const listRequests: string[] = [];
      page.on('request', (request) => {
        if (new URL(request.url()).pathname.endsWith('/notifications')) {
          listRequests.push(request.url());
        }
      });

      await notificationsPage.filterChip('Comments').click();
      await page.waitForTimeout(500);

      expect(listRequests).toEqual([]);
      await expect(entries(notificationsPage).comment).toHaveCount(1);
    });
  });

  test('applies a chip from the keyboard with a visible focus ring', async ({
    browser,
  }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage, page) => {
      const allChip = notificationsPage.filterChip('All');
      const unreadChip = notificationsPage.filterChip('Unread');

      await allChip.focus();
      await allChip.press('Tab');
      await expect(unreadChip).toBeFocused();
      await expect(unreadChip).not.toHaveCSS('box-shadow', 'none');

      const listedUnread = page.waitForResponse(
        (response) =>
          new URL(response.url()).searchParams.get('unread') === 'true',
      );
      await unreadChip.press('Enter');
      await listedUnread;

      await expectOnlySelected(notificationsPage, 'Unread');
    });
  });

  test('drops a stale response when the chip changes mid-flight', async ({
    browser,
  }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage, page) => {
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');

      await page.route(
        /\/notifications\?.*type=added_to_bookmarks/,
        async (route) => {
          await new Promise((resolve) => setTimeout(resolve, 1500));
          await route.continue();
        },
      );
      const { bookmark, comment, reply } = entries(notificationsPage);

      const staleLanded = page.waitForResponse(
        (response) =>
          new URL(response.url()).searchParams.get('type') ===
          'added_to_bookmarks',
      );

      await notificationsPage.filterChip('Bookmarks').click();
      await expect(comment).toHaveCount(0);
      await expect(reply).toHaveCount(0);

      await notificationsPage.selectFilter('Replies');
      const stale = await staleLanded;
      await stale.finished();

      await expectOnlySelected(notificationsPage, 'Replies');
      await expect(reply).toHaveCount(1);
      await expect(bookmark).toHaveCount(0);
    });
  });

  test('keeps the unread count global and hides entries read before filtering', async ({
    browser,
  }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage) => {
      const { bookmark, comment, reply } = entries(notificationsPage);

      await comment.hover();
      await notificationsPage.markReadButton(comment).click();
      await expect(notificationsPage.unreadSubtitle).toHaveText('2 unread');

      const query = await notificationsPage.selectFilter('Unread');
      expect(query.get('unread')).toBe('true');
      expect(query.has('type')).toBe(false);
      await expect(comment).toHaveCount(0);
      await expect(bookmark).toHaveCount(1);
      await expect(reply).toHaveCount(1);
      await expect(notificationsPage.markReadButtons).toHaveCount(2);

      for (const label of ['Bookmarks', 'Replies', 'All'] as const) {
        await notificationsPage.selectFilter(label);
        await expect(notificationsPage.unreadSubtitle).toHaveText('2 unread');
      }
    });
  });

  test('marks everything read under Unread and empties the filter on reload', async ({
    browser,
  }) => {
    const owner = await seedMixed();

    await withFiltersPage(browser, owner, async (notificationsPage, page) => {
      const { bookmark, comment, reply } = entries(notificationsPage);
      await notificationsPage.selectFilter('Unread');

      const markedAll = page.waitForRequest((request) =>
        request.url().includes('/notifications/mark-all-read'),
      );
      await notificationsPage.markAllReadButton.click();
      expect(new URL((await markedAll).url()).search).toBe('');

      await expect(notificationsPage.unreadSubtitle).toHaveCount(0);
      await expect(notificationsPage.markAllReadButton).toHaveCount(0);
      await expect(notificationsPage.markReadButtons).toHaveCount(0);
      await expect(bookmark).toHaveCount(1);

      await notificationsPage.selectFilter('All');
      await expect(bookmark).toHaveCount(1);
      await expect(comment).toHaveCount(1);
      await expect(reply).toHaveCount(1);
      await expect(notificationsPage.markReadButtons).toHaveCount(0);

      await notificationsPage.selectFilter('Unread');
      await expect(notificationsPage.filteredEmptyHeading).toBeVisible();
      await expect(notificationsPage.filterGroup).toBeVisible();
    });
  });

  test('shows an empty filter state and keeps the chips usable', async ({
    browser,
  }) => {
    const owner = unique('filter-bookmarks-only');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(unique('filter-reader'));
    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    await readerApi.content.bookmarkImage(imageId);

    await withFiltersPage(browser, owner, async (notificationsPage) => {
      const { bookmark } = entries(notificationsPage);

      for (const label of ['Replies', 'Comments'] as const) {
        await notificationsPage.selectFilter(label);
        await expect(notificationsPage.filteredEmptyHeading).toBeVisible();
        await expect(notificationsPage.emptyHeading).toHaveCount(0);
        await expect(bookmark).toHaveCount(0);
        await expect(notificationsPage.loadMoreButton).toHaveCount(0);
        await expectOnlySelected(notificationsPage, label);
      }

      await notificationsPage.selectFilter('All');
      await expect(bookmark).toHaveCount(1);
      await expect(notificationsPage.filteredEmptyHeading).toHaveCount(0);
    });
  });

  test('hides the chips for a user without notifications', async ({
    browser,
  }) => {
    const owner = unique('filter-empty');
    await provisionUser(owner);

    await withFiltersPage(browser, owner, async (notificationsPage) => {
      await expect(notificationsPage.emptyHeading).toBeVisible();
      await expect(notificationsPage.filterGroup).toHaveCount(0);
    });
  });

  test('pages within the filtered set', async ({ browser }) => {
    const owner = unique('filter-paged');
    const ownerApi = await provisionUser(owner);
    const commenterApi = await provisionUser(unique('filter-commenter'));
    const readerApi = await provisionUser(unique('filter-reader'));
    const commentedImage = await ownerApi.content.uploadImage({
      isPublic: true,
    });
    const bookmarkedImage = await ownerApi.content.uploadImage({
      isPublic: true,
    });

    await readerApi.content.bookmarkImage(bookmarkedImage);
    for (let index = 1; index <= 52; index++) {
      await commenterApi.content.createComment(
        commentedImage,
        `c-${String(index).padStart(2, '0')}`,
      );
    }

    await withFiltersPage(browser, owner, async (notificationsPage) => {
      const { bookmark } = entries(notificationsPage);

      await notificationsPage.selectFilter('Bookmarks');
      await expect(bookmark).toHaveCount(1);
      await expect(notificationsPage.loadMoreButton).toHaveCount(0);

      const firstPage = await notificationsPage.selectFilter('Comments');
      expect(firstPage.get('page')).toBe('1');
      await expect(notificationsPage.loadMoreButton).toBeVisible();

      const listed = notificationsPage.waitForList();
      await notificationsPage.loadMoreButton.click();
      const secondPage = new URL((await listed).url()).searchParams;
      expect(secondPage.get('page')).toBe('2');
      expect(secondPage.get('type')).toBe('comment');
      await expect(notificationsPage.loadMoreButton).toHaveCount(0);
      await expect(bookmark).toHaveCount(0);

      const allQuery = await notificationsPage.selectFilter('All');
      expect(allQuery.get('page')).toBe('1');
      expect(allQuery.has('type')).toBe(false);
      await expect(notificationsPage.loadMoreButton).toBeVisible();
    });
  });
});
