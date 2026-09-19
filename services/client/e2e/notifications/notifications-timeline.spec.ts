import type { Browser, BrowserContext, Locator, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { type Account, unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';
import { ExplorePage } from '../pages/ExplorePage';
import { NotificationsPage } from '../pages/NotificationsPage';

const REPLY_LABELS = ['first reply', 'second reply'] as const;

type ReplyLabel = (typeof REPLY_LABELS)[number];

interface Timeline {
  owner: Account;
  firstReader: Account;
  secondReader: Account;
  thirdReader: Account;
  imageId: string;
  plainCommentId: string;
  replyIds: Record<ReplyLabel, string>;
}

async function seedTimeline(): Promise<Timeline> {
  const owner = unique('notif-owner');
  const firstReader = unique('notif-first');
  const secondReader = unique('notif-second');
  const thirdReader = unique('notif-third');

  const ownerApi = await provisionUser(owner);
  const firstReaderApi = await provisionUser(firstReader);
  const secondReaderApi = await provisionUser(secondReader);
  const thirdReaderApi = await provisionUser(thirdReader);

  const imageId = await ownerApi.content.uploadImage({ isPublic: true });
  await firstReaderApi.content.bookmarkImage(imageId);
  await secondReaderApi.content.bookmarkImage(imageId);
  await thirdReaderApi.content.bookmarkImage(imageId);

  const ownerCommentId = await ownerApi.content.createComment(
    imageId,
    'owner note',
  );
  const replyIds = {
    'first reply': await firstReaderApi.content.createReply(
      imageId,
      ownerCommentId,
      'first reply',
    ),
    'second reply': await secondReaderApi.content.createReply(
      imageId,
      ownerCommentId,
      'second reply',
    ),
  };
  const plainCommentId = await firstReaderApi.content.createComment(
    imageId,
    'a plain comment',
  );

  return {
    owner,
    firstReader,
    secondReader,
    thirdReader,
    imageId,
    plainCommentId,
    replyIds,
  };
}

async function withTimelinePage(
  browser: Browser,
  owner: Account,
  run: (notificationsPage: NotificationsPage, page: Page) => Promise<void>,
) {
  let context: BrowserContext | undefined;

  try {
    context = await signInContext(browser, owner);
    const page = await context.newPage();
    const notificationsPage = new NotificationsPage(page);
    await notificationsPage.goto();
    await run(notificationsPage, page);
  } finally {
    await context?.close();
  }
}

async function visibleReplies(entry: Locator): Promise<ReplyLabel[]> {
  const visible: ReplyLabel[] = [];

  for (const label of REPLY_LABELS) {
    if (await entry.getByText(label, { exact: true }).isVisible()) {
      visible.push(label);
    }
  }

  return visible;
}

async function resolveLatestReply(entry: Locator): Promise<ReplyLabel> {
  await expect.poll(() => visibleReplies(entry)).toHaveLength(1);
  const [latestReply] = await visibleReplies(entry);

  return latestReply;
}

function otherReply(label: ReplyLabel): ReplyLabel {
  if (label === 'first reply') {
    return 'second reply';
  }

  return 'first reply';
}

test.describe('Notifications timeline', () => {
  test('groups bookmarks, replies and comments into timeline entries', async ({
    browser,
  }) => {
    const { owner, firstReader, secondReader, thirdReader } =
      await seedTimeline();
    const readers = [firstReader, secondReader, thirdReader]
      .map((reader) => reader.username)
      .join('|');

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      await expect(notificationsPage.heading).toBeVisible();
      await expect(notificationsPage.unreadSubtitle).toHaveText('6 unread');
      await expect(notificationsPage.markAllReadButton).toBeVisible();

      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      await expect(bookmarkEntry).toHaveCount(1);
      await expect(notificationsPage.entrySentence(bookmarkEntry)).toHaveText(
        new RegExp(`^(${readers}), (${readers}) and 1 other bookmarked$`),
      );

      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      await expect(replyEntry).toHaveCount(1);

      const latestReply = await resolveLatestReply(replyEntry);
      const earlierReply = replyEntry.getByText(otherReply(latestReply), {
        exact: true,
      });
      const toggle = notificationsPage.threadToggle(replyEntry);

      await expect(earlierReply).toBeHidden();
      await expect(toggle).toHaveText('1 earlier reply');
      await expect(toggle).toHaveAttribute('aria-expanded', 'false');

      await toggle.click();
      await expect(earlierReply).toBeVisible();
      await expect(toggle).toHaveText('Hide earlier reply');
      await expect(toggle).toHaveAttribute('aria-expanded', 'true');
      await expect(
        notificationsPage.hideEarlierButton(replyEntry),
      ).toBeVisible();
      await expect(
        replyEntry.getByRole('button', { name: 'Show less' }),
      ).toHaveCount(0);

      await toggle.click();
      await expect(earlierReply).toBeHidden();
      await expect(toggle).toHaveText('1 earlier reply');
      await expect(toggle).toHaveAttribute('aria-expanded', 'false');

      const commentEntry = notificationsPage.entryByText(/\bcommented\b/);
      await expect(commentEntry).toHaveCount(1);
      await expect(
        commentEntry.getByText('a plain comment', { exact: true }),
      ).toBeVisible();
      await expect(notificationsPage.threadToggle(commentEntry)).toHaveCount(0);
      await expect(
        notificationsPage.commentThreadToggle(commentEntry),
      ).toHaveCount(0);
      await expect(
        notificationsPage.hideEarlierButton(commentEntry),
      ).toHaveCount(0);
    });
  });

  test('lists every bookmarker behind the bookmark disclosure', async ({
    browser,
  }) => {
    const { owner, firstReader, secondReader, thirdReader } =
      await seedTimeline();
    const usernames = [firstReader, secondReader, thirdReader].map(
      (reader) => reader.username,
    );

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      const showAll = notificationsPage.showAllButton(bookmarkEntry);
      const nameCount = async () => {
        let total = 0;

        for (const username of usernames) {
          total += await bookmarkEntry
            .getByText(username, { exact: true })
            .filter({ visible: true })
            .count();
        }

        return total;
      };

      await expect(showAll).toHaveText('Show all 3');
      await expect(showAll).toHaveAttribute('aria-expanded', 'false');
      await expect(notificationsPage.visibleTimes(bookmarkEntry)).toHaveCount(
        1,
      );
      await expect.poll(nameCount).toBe(2);

      await showAll.click();

      const showLess = notificationsPage.showLessButton(bookmarkEntry);
      await expect(showLess).toBeVisible();
      await expect(showLess).toHaveAttribute('aria-expanded', 'true');

      for (const username of usernames) {
        await expect(
          bookmarkEntry.getByText(username, { exact: true }).last(),
        ).toBeVisible();
      }

      await expect.poll(nameCount).toBe(5);
      await expect(notificationsPage.visibleTimes(bookmarkEntry)).toHaveCount(
        4,
      );

      await bookmarkEntry
        .getByText(thirdReader.username, { exact: true })
        .last()
        .click();
      await expect(page).toHaveURL(/\/notifications$/);

      await showLess.click();
      await expect(showAll).toHaveText('Show all 3');
    });
  });

  test('shows no bookmark disclosure for a single bookmarker', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const reader = unique('notif-reader');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(reader);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    await readerApi.content.bookmarkImage(imageId);

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);

      await expect(bookmarkEntry).toBeVisible();
      await expect(notificationsPage.showAllButton(bookmarkEntry)).toHaveCount(
        0,
      );
    });
  });

  test('toggles the reply disclosure from the keyboard', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      const toggle = notificationsPage.threadToggle(replyEntry);

      await toggle.focus();
      await toggle.press('Enter');
      await expect(toggle).toHaveAttribute('aria-expanded', 'true');
      await expect(toggle).toHaveText('Hide earlier reply');
      await expect(toggle).toBeFocused();

      await toggle.press('Space');
      await expect(toggle).toHaveAttribute('aria-expanded', 'false');
      await expect(toggle).toHaveText('1 earlier reply');
      await expect(toggle).toBeFocused();
    });
  });

  test('shows each comment time once across the entry and its thread rows', async ({
    browser,
  }) => {
    const { owner, firstReader, secondReader } = await seedTimeline();
    const replyAuthors: Record<ReplyLabel, Account> = {
      'first reply': firstReader,
      'second reply': secondReader,
    };

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const commentEntry = notificationsPage.entryByText(/\bcommented\b/);
      await expect(
        notificationsPage
          .threadRow(commentEntry, firstReader.username)
          .locator('time'),
      ).toHaveCount(0);
      await expect(notificationsPage.visibleTimes(commentEntry)).toHaveCount(1);

      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      const latestReply = await resolveLatestReply(replyEntry);
      const earlierReply = otherReply(latestReply);

      await expect(
        notificationsPage
          .threadRow(replyEntry, replyAuthors[latestReply].username)
          .locator('time'),
      ).toHaveCount(0);
      await expect(notificationsPage.visibleTimes(replyEntry)).toHaveCount(1);

      await notificationsPage.threadToggle(replyEntry).click();

      const earlierTime = notificationsPage
        .threadRow(replyEntry, replyAuthors[earlierReply].username)
        .locator('time');
      await expect(earlierTime).toBeVisible();
      await expect(earlierTime).toHaveAttribute(
        'datetime',
        /^\d{4}-\d{2}-\d{2}T/,
      );
      await expect(earlierTime).toHaveAttribute('title', /\S/);
      await expect(notificationsPage.visibleTimes(replyEntry)).toHaveCount(2);
    });
  });

  test('labels the comment disclosure by its earlier comments', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const reader = unique('notif-reader');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(reader);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    await readerApi.content.createComment(imageId, 'first comment');
    await readerApi.content.createComment(imageId, 'second comment');
    await readerApi.content.createComment(imageId, 'third comment');

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const commentEntry = notificationsPage.entryByText(/\bcommented\b/);
      const toggle = notificationsPage.commentThreadToggle(commentEntry);

      await expect(toggle).toHaveText('2 earlier comments');
      await expect(toggle).toHaveAttribute('aria-expanded', 'false');

      await toggle.click();
      await expect(toggle).toHaveText('Hide earlier comments');
      await expect(toggle).toHaveAttribute('aria-expanded', 'true');

      await toggle.click();
      await expect(toggle).toHaveText('2 earlier comments');
      await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    });
  });

  test('marks a single entry read without leaving the page', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await expect(notificationsPage.unreadSubtitle).toHaveText('6 unread');

      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      const markRead = notificationsPage.markReadButton(bookmarkEntry);

      await bookmarkEntry.hover();
      await markRead.click();

      await expect(markRead).toHaveCount(0);
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');
      await expect(page).toHaveURL(/\/notifications$/);
      await expect(bookmarkEntry).toBeVisible();

      await notificationsPage.reload();
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');
    });
  });

  test('keeps focus inside the entry when marking it read by keyboard', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      const markRead = notificationsPage.markReadButton(bookmarkEntry);

      await markRead.focus();
      await markRead.press('Enter');

      await expect(markRead).toHaveCount(0);
      await expect(notificationsPage.unreadSubtitle).toHaveText('3 unread');
      await expect(
        notificationsPage.openPostButton(bookmarkEntry),
      ).toBeFocused();
    });
  });

  test('marks every entry read at once', async ({ browser }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      await expect(notificationsPage.unreadSubtitle).toHaveText('6 unread');

      await notificationsPage.markAllReadButton.click();

      await expect(notificationsPage.markAllReadButton).toHaveCount(0);
      await expect(notificationsPage.unreadSubtitle).toHaveCount(0);
      await expect(notificationsPage.markReadButtons).toHaveCount(0);
      await expect(notificationsPage.entryByText(/bookmarked/)).toBeVisible();

      await notificationsPage.reload();
      await expect(notificationsPage.entryByText(/bookmarked/)).toBeVisible();
      await expect(notificationsPage.markReadButtons).toHaveCount(0);
      await expect(notificationsPage.markAllReadButton).toHaveCount(0);
      await expect(notificationsPage.unreadSubtitle).toHaveCount(0);
    });
  });

  test('opens the explore viewer at the comment from an entry', async ({
    browser,
  }) => {
    const { owner, imageId, plainCommentId } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const commentEntry = notificationsPage.entryByText(/\bcommented\b/);

      await notificationsPage.openPostButton(commentEntry).click();

      await expect(page).toHaveURL(
        new RegExp(`/explore\\?post=${imageId}&comment=${plainCommentId}$`),
      );
      await expect(new ExplorePage(page).viewer).toBeVisible();
    });
  });

  test('opens the explore viewer at a reply from its thread row', async ({
    browser,
  }) => {
    const { owner, firstReader, secondReader, imageId, replyIds } =
      await seedTimeline();
    const replyAuthors: Record<ReplyLabel, Account> = {
      'first reply': firstReader,
      'second reply': secondReader,
    };

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      const latestReply = await resolveLatestReply(replyEntry);
      const latestReplyRow = replyEntry.getByRole('button', {
        name: replyAuthors[latestReply].username,
        exact: true,
      });

      await expect(latestReplyRow).toHaveAccessibleDescription(latestReply);
      await latestReplyRow.click();

      await expect(page).toHaveURL(
        new RegExp(
          `/explore\\?post=${imageId}&comment=${replyIds[latestReply]}$`,
        ),
      );
    });
  });

  test('routes thread row clicks to the comment and hashtag clicks to search', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const reader = unique('notif-reader');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(reader);
    const hashtag = 'e2etag';

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    const ownerCommentId = await ownerApi.content.createComment(
      imageId,
      'owner note',
    );
    const replyId = await readerApi.content.createReply(
      imageId,
      ownerCommentId,
      `nice catch #${hashtag} indeed`,
    );

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      const plainText = replyEntry.getByText('nice catch');
      const hashtagChip = replyEntry.getByRole('button', {
        name: `#${hashtag}`,
      });

      await expect(hashtagChip).toBeVisible();
      const textBox = await plainText.boundingBox();
      expect(textBox).not.toBeNull();
      await page.mouse.click(textBox!.x + 4, textBox!.y + textBox!.height / 2);

      await expect(page).toHaveURL(
        new RegExp(`/explore\\?post=${imageId}&comment=${replyId}$`),
      );

      await notificationsPage.goto();
      await hashtagChip.click();

      await expect(page).toHaveURL(
        new RegExp(`/explore\\?search=%23${hashtag}&searchBy=hashtag$`),
      );
    });
  });
});
