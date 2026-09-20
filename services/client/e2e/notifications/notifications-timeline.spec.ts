import type { Browser, BrowserContext, Locator, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { Locale } from '../helpers/Locale';
import { type Account, unique } from '../helpers/accounts';
import { ApiClient } from '../helpers/api';
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
      await expect(notificationsPage.seeAllButton(replyEntry)).toHaveCount(0);

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

  test('completes the bookmark sentence and lists every bookmarker in the actor card', async ({
    browser,
  }) => {
    const { owner, firstReader, secondReader, thirdReader } =
      await seedTimeline();
    const usernames = [firstReader, secondReader, thirdReader].map(
      (reader) => reader.username,
    );
    const readers = usernames.join('|');
    const sentence = new RegExp(
      `^(${readers}), (${readers}) and 1 other bookmarked$`,
    );

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      const seeAll = notificationsPage.seeAllButton(bookmarkEntry);
      const card = notificationsPage.actorCard;

      await expect(seeAll).toHaveText('See all 3');
      await expect(seeAll).toHaveAttribute('aria-expanded', 'false');
      await expect(notificationsPage.entrySentence(bookmarkEntry)).toHaveText(
        sentence,
      );
      await expect(notificationsPage.visibleTimes(bookmarkEntry)).toHaveCount(
        1,
      );

      await notificationsPage.clickUntil(seeAll, card);
      await expect(seeAll).toHaveAttribute('aria-expanded', 'true');
      await expect(notificationsPage.actorCardRows).toHaveCount(3);

      for (const username of usernames) {
        await expect(card.getByText(username, { exact: true })).toBeVisible();
      }

      await expect(card.locator('time')).toHaveCount(0);
      await expect(notificationsPage.visibleTimes(bookmarkEntry)).toHaveCount(
        1,
      );
      await expect(notificationsPage.entrySentence(bookmarkEntry)).toHaveText(
        sentence,
      );

      await page.keyboard.press('Escape');
      await expect(card).toBeHidden();
      await expect(page).toHaveURL(/\/notifications$/);
    });
  });

  test('opens the bookmark actor card on hover and from the keyboard', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      const seeAll = notificationsPage.seeAllButton(bookmarkEntry);
      const card = notificationsPage.actorCard;

      await seeAll.focus();
      await page.keyboard.press('Enter');
      await expect(card).toBeVisible();
      await expect(seeAll).toHaveAttribute('aria-expanded', 'true');

      await page.keyboard.press('Escape');
      await expect(card).toBeHidden();
      await expect(seeAll).toBeFocused();

      await seeAll.hover();
      await expect(card).toBeVisible();
      await expect(notificationsPage.actorCardRows).toHaveCount(3);

      await page.mouse.move(0, 0);
      await expect(card).toBeHidden();
    });
  });

  test('shows no actor toggle for a single bookmarker', async ({ browser }) => {
    const owner = unique('notif-owner');
    const reader = unique('notif-reader');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(reader);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    await readerApi.content.bookmarkImage(imageId);

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);

      await expect(bookmarkEntry).toBeVisible();
      await expect(notificationsPage.seeAllButton(bookmarkEntry)).toHaveCount(
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

      const latestAuthorBox = await notificationsPage
        .threadRow(replyEntry, replyAuthors[latestReply].username)
        .getByText(replyAuthors[latestReply].username, { exact: true })
        .boundingBox();
      expect(latestAuthorBox).not.toBeNull();
      expect(latestAuthorBox!.width).toBeGreaterThan(1);

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

  test('hides the repeated author name in a single-author thread', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const reader = unique('notif-reader');
    const ownerApi = await provisionUser(owner);
    const readerApi = await provisionUser(reader);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    const commentIds: Record<string, string> = {
      'first single': await readerApi.content.createComment(
        imageId,
        'first single',
      ),
      'second single': await readerApi.content.createComment(
        imageId,
        'second single',
      ),
    };

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const entry = notificationsPage.entryByText(/\bcommented\b/);
      const row = notificationsPage.threadRow(entry, reader.username);

      await expect(notificationsPage.entrySentence(entry)).toContainText(
        reader.username,
      );
      await expect(row).toHaveCount(1);
      await expect
        .poll(() => row.textContent())
        .toMatch(new RegExp(`${reader.username}\\s`));

      const latestText = row.getByText(/^(first|second) single$/);
      await expect(latestText).toBeVisible();
      const latestCommentId = commentIds[(await latestText.innerText()).trim()];

      const nameBox = await row
        .getByText(reader.username, { exact: true })
        .boundingBox();
      expect(nameBox).not.toBeNull();
      expect(nameBox!.width).toBeLessThanOrEqual(1);

      const textBox = await latestText.boundingBox();
      expect(textBox).not.toBeNull();
      await page.mouse.click(textBox!.x + 4, textBox!.y + textBox!.height / 2);

      await expect(page).toHaveURL(
        new RegExp(`/explore\\?post=${imageId}&comment=${latestCommentId}$`),
      );
      await expect(new ExplorePage(page).viewer).toBeVisible();
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
      await expect(notificationsPage.markReadButton(bookmarkEntry)).toHaveCount(
        0,
      );
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

  test('wraps long author names at phone width without overflow', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const otherCommenter = unique('notif-other');
    const commenter = unique('notif-long-commenter');
    expect(commenter.username.length).toBeGreaterThanOrEqual(20);

    const ownerApi = await provisionUser(owner);
    const otherCommenterApi = await provisionUser(otherCommenter);
    const commenterApi = await provisionUser(commenter);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    await otherCommenterApi.content.createComment(imageId, 'an earlier word');
    await commenterApi.content.createComment(
      imageId,
      'this comment keeps going long enough to wrap across several lines on a phone #e2ewrap and then ends',
    );

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await page.setViewportSize({ width: 390, height: 844 });

      const entry = notificationsPage.entryByText(/\bcommented\b/);
      await notificationsPage.commentThreadToggle(entry).click();

      const row = notificationsPage.threadRow(entry, commenter.username);
      const text = row.getByText('this comment keeps going', { exact: false });
      const chip = row.getByRole('button', { name: '#e2ewrap' });

      await expect(text).toBeVisible();
      await expect(chip).toBeVisible();

      const rowBox = await row.boundingBox();
      const textBox = await text.boundingBox();
      const chipBox = await chip.boundingBox();
      expect(rowBox).not.toBeNull();
      expect(textBox).not.toBeNull();
      expect(chipBox).not.toBeNull();

      expect(textBox!.width).toBeGreaterThanOrEqual(rowBox!.width / 2);
      expect(chipBox!.x + chipBox!.width).toBeLessThanOrEqual(
        rowBox!.x + rowBox!.width + 0.5,
      );

      const nameBox = await row
        .getByText(commenter.username, { exact: true })
        .boundingBox();
      expect(nameBox).not.toBeNull();
      expect(nameBox!.width).toBeGreaterThan(1);
    });
  });
});

const NOW = /^now$/;
const MINUTES = /^\d{1,2}m$/;
const HOURS = /^\d{1,2}h$/;
const CLOCK = /^\d{1,2}:\d{2}/;
const FULL_DATE_TIME = /\d{4}.*\d:\d{2}/;
const MINUTE_MS = 60_000;
const DAY_MS = 24 * 60 * MINUTE_MS;
const MORNING_HOUR = 6;

async function latestCreatedAt(page: Page): Promise<Date> {
  const times = page.getByRole('main').locator('time');
  await expect(times.first()).toBeAttached();
  const stamps = await times.evaluateAll((elements) =>
    elements.map((element) => Date.parse(element.getAttribute('datetime')!)),
  );

  return new Date(Math.max(...stamps));
}

async function pinClock(
  notificationsPage: NotificationsPage,
  page: Page,
  pin: (createdAt: Date) => Date,
) {
  const createdAt = await latestCreatedAt(page);
  const cdp = await page.context().newCDPSession(page);
  await cdp.send('Emulation.setTimezoneOverride', {
    timezoneId: morningTimeZone(createdAt),
  });
  await page.clock.install({ time: pin(createdAt) });
  await notificationsPage.goto();
  await page.mouse.move(0, 0);
}

function sameMoment(createdAt: Date): Date {
  return createdAt;
}

function morningTimeZone(createdAt: Date): string {
  const offset = (MORNING_HOUR - createdAt.getUTCHours() + 24) % 24;

  if (offset > 14) {
    return `Etc/GMT+${24 - offset}`;
  }

  return `Etc/GMT-${offset}`;
}

function daysLater(days: number): (createdAt: Date) => Date {
  return (createdAt) => new Date(createdAt.getTime() + days * DAY_MS);
}

test.describe('Notifications relative times', () => {
  test('shows today times relative to now and advances them every minute', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await pinClock(notificationsPage, page, sameMoment);

      let navigations = 0;
      page.on('framenavigated', () => navigations++);

      const time = notificationsPage.visibleTimes(
        notificationsPage.entryByText(/\bcommented\b/),
      );

      await expect(time).toHaveText(NOW);
      await expect(time).toHaveAttribute('datetime', /^\d{4}-\d{2}-\d{2}T/);
      await expect(time).toHaveAttribute('title', FULL_DATE_TIME);

      await page.clock.runFor(MINUTE_MS);
      await expect(time).toHaveText('1m');

      await page.clock.runFor(34 * MINUTE_MS);
      await expect(time).toHaveText('35m');

      await page.clock.runFor(95 * MINUTE_MS);
      await expect(time).toHaveText('2h');
      await expect(time).toHaveAttribute('title', FULL_DATE_TIME);

      expect(navigations).toBe(0);
    });
  });

  test('advances thread row times with the entry time', async ({ browser }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await pinClock(notificationsPage, page, sameMoment);

      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      await notificationsPage.threadToggle(replyEntry).click();
      await page.mouse.move(0, 0);

      const times = notificationsPage.visibleTimes(replyEntry);
      await expect(times).toHaveText([NOW, NOW]);

      await page.clock.runFor(35 * MINUTE_MS);
      await expect(times).toHaveText([MINUTES, MINUTES]);

      for (const time of await times.all()) {
        await expect(time).toHaveAttribute('title', FULL_DATE_TIME);
      }
    });
  });

  test('keeps the clock time for yesterday', async ({ browser }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await pinClock(notificationsPage, page, daysLater(1));

      const time = notificationsPage.visibleTimes(
        notificationsPage.entryByText(/\bcommented\b/),
      );

      await expect(time).toHaveText(CLOCK);
      await expect(time).toHaveAttribute('title', FULL_DATE_TIME);
    });
  });

  test('keeps the short date for older days', async ({ browser }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await pinClock(notificationsPage, page, daysLater(8));

      const time = notificationsPage.visibleTimes(
        notificationsPage.entryByText(/\bcommented\b/),
      );

      await expect(time).toHaveText(/\S/);
      for (const format of [NOW, MINUTES, HOURS, CLOCK]) {
        await expect(time).not.toHaveText(format);
      }
      await expect(time).toHaveAttribute('title', FULL_DATE_TIME);
    });
  });

  test('localises today times for uk', async ({ browser }) => {
    const { owner } = await seedTimeline();
    const ownerApi = await ApiClient.createForUser(
      owner.username,
      owner.password,
    );

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await new Locale(page, ownerApi).set('uk');
      await pinClock(notificationsPage, page, sameMoment);

      const time = page.getByRole('main').locator('time:visible').first();

      await expect(time).toHaveText(/\S/);
      await expect(time).not.toHaveText(NOW);
      await expect(time).not.toHaveText(CLOCK);

      await page.clock.runFor(35 * MINUTE_MS);
      await expect(time).toHaveText(/35/);
      await expect(time).not.toHaveText('35m');
      await expect(time).toHaveAttribute('title', FULL_DATE_TIME);
    });
  });

  test('keeps a single entry time while the bookmark actor card is open', async ({
    browser,
  }) => {
    const { owner } = await seedTimeline();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await pinClock(notificationsPage, page, sameMoment);

      let navigations = 0;
      page.on('framenavigated', () => navigations++);

      const bookmarkEntry = notificationsPage.entryByText(/bookmarked/);
      await notificationsPage.clickUntil(
        notificationsPage.seeAllButton(bookmarkEntry),
        notificationsPage.actorCard,
      );
      await page.mouse.move(0, 0);

      await expect(notificationsPage.actorCard.locator('time')).toHaveCount(0);

      const times = notificationsPage.visibleTimes(bookmarkEntry);
      await expect(times).toHaveText([NOW]);
      await expect(times).not.toHaveText([CLOCK]);

      await page.clock.runFor(35 * MINUTE_MS);
      await expect(times).toHaveText(['35m']);
      await expect(times).toHaveText([MINUTES]);
      await expect(times).toHaveAttribute('title', FULL_DATE_TIME);
      await expect(times).toHaveAttribute('datetime', /^\d{4}-\d{2}-\d{2}T/);

      expect(navigations).toBe(0);
    });
  });
});

const QUOTE_LABEL = /your comment:/;

interface QuoteSeed {
  owner: Account;
  ownerApi: ApiClient;
  imageId: string;
  parentId: string;
  parent: string;
}

async function seedQuotedReply(): Promise<QuoteSeed> {
  const owner = unique('notif-owner');
  const reader = unique('notif-reader');
  const ownerApi = await provisionUser(owner);
  const readerApi = await provisionUser(reader);

  const imageId = await ownerApi.content.uploadImage({ isPublic: true });
  const parent = `owner parent ${Date.now()} with enough words to need truncation at phone width`;
  const parentId = await ownerApi.content.createComment(imageId, parent);
  await readerApi.content.createReply(imageId, parentId, 'reader reply');

  return { owner, ownerApi, imageId, parentId, parent };
}

function quoteLine(entry: Locator): Locator {
  return entry.getByText(QUOTE_LABEL).locator('..');
}

test.describe('Notifications parent comment quote', () => {
  test('quotes the parent comment above a reply thread', async ({
    browser,
  }) => {
    const { owner, imageId, parentId, parent } = await seedQuotedReply();
    const secondReader = unique('notif-second');
    const commenter = unique('notif-commenter');
    const secondReaderApi = await provisionUser(secondReader);
    const commenterApi = await provisionUser(commenter);
    await secondReaderApi.content.createReply(
      imageId,
      parentId,
      'second reply',
    );
    await commenterApi.content.createComment(imageId, 'a plain comment');

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );
      const quote = quoteLine(replyEntry);

      await expect(replyEntry).toHaveCount(1);
      await expect(notificationsPage.entrySentence(replyEntry)).toHaveCount(1);
      await expect(replyEntry.getByText(QUOTE_LABEL)).toHaveCount(1);
      await expect(quote).toBeVisible();
      await expect(quote.locator('svg')).toHaveCount(1);
      await expect(quote).toContainText(parent);
      await expect(quote).not.toHaveAttribute('title', /\S/);
      await expect(quote.locator('[data-hashtag], button, a')).toHaveCount(0);

      const quoteBox = await quote.boundingBox();
      const rowBox = await notificationsPage
        .threadRow(replyEntry, secondReader.username)
        .boundingBox();
      expect(quoteBox).not.toBeNull();
      expect(rowBox).not.toBeNull();
      expect(Math.abs(quoteBox!.x - rowBox!.x)).toBeLessThanOrEqual(1);

      await notificationsPage.threadToggle(replyEntry).click();
      await expect(replyEntry.getByText(QUOTE_LABEL)).toHaveCount(1);

      const commentEntry = notificationsPage.entryByText(/\bcommented\b/);
      await expect(commentEntry).toHaveCount(1);
      await expect(commentEntry.getByText(QUOTE_LABEL)).toHaveCount(0);

      await page.setViewportSize({ width: 390, height: 844 });
      const metrics = await quote.evaluate((element) => ({
        scrollWidth: element.scrollWidth,
        clientWidth: element.clientWidth,
        height: element.getBoundingClientRect().height,
        lineHeight: parseFloat(getComputedStyle(element).lineHeight),
      }));
      expect(metrics.scrollWidth).toBeGreaterThan(metrics.clientWidth);
      expect(metrics.height).toBeLessThanOrEqual(metrics.lineHeight * 1.5);
    });
  });

  test('drops the quote once the parent comment is deleted', async ({
    browser,
  }) => {
    const { owner, ownerApi, parentId } = await seedQuotedReply();
    await ownerApi.content.deleteComment(parentId);

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );

      await expect(notificationsPage.entrySentence(replyEntry)).toBeVisible();
      await expect(
        replyEntry.getByText('reader reply', { exact: true }),
      ).toBeVisible();
      await expect(replyEntry.getByText(QUOTE_LABEL)).toHaveCount(0);
      await expect(replyEntry).not.toContainText(
        /deleted|removed|unavailable/i,
      );
    });
  });

  test('quotes nothing when replies answer different parents', async ({
    browser,
  }) => {
    const owner = unique('notif-owner');
    const firstReader = unique('notif-first');
    const secondReader = unique('notif-second');
    const ownerApi = await provisionUser(owner);
    const firstReaderApi = await provisionUser(firstReader);
    const secondReaderApi = await provisionUser(secondReader);

    const imageId = await ownerApi.content.uploadImage({ isPublic: true });
    const parentA = `parent a ${Date.now()}`;
    const parentB = `parent b ${Date.now()}`;
    const parentAId = await ownerApi.content.createComment(imageId, parentA);
    const parentBId = await ownerApi.content.createComment(imageId, parentB);
    await firstReaderApi.content.createReply(imageId, parentAId, 'reply a');
    await secondReaderApi.content.createReply(imageId, parentBId, 'reply b');

    await withTimelinePage(browser, owner, async (notificationsPage) => {
      const replyEntry = notificationsPage.entryByText(
        'replied to your comment',
      );

      await expect(replyEntry).toHaveCount(1);
      await expect(replyEntry.getByText(QUOTE_LABEL)).toHaveCount(0);
      await expect(replyEntry).not.toContainText(parentA);
      await expect(replyEntry).not.toContainText(parentB);
    });
  });

  test('translates the quote label for uk', async ({ browser }) => {
    const { owner, ownerApi, parent } = await seedQuotedReply();

    await withTimelinePage(browser, owner, async (notificationsPage, page) => {
      await new Locale(page, ownerApi).set('uk');
      await notificationsPage.goto();

      const quote = page.getByRole('main').getByText(parent, { exact: false });

      await expect(quote).toBeVisible();
      await expect(quote.locator('svg')).toHaveCount(1);
      await expect(quote).toContainText('ваш коментар:');
      await expect(page.getByRole('main').getByText(QUOTE_LABEL)).toHaveCount(
        0,
      );
    });
  });
});
