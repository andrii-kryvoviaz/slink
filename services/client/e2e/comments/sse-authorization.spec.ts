import type { Browser, BrowserContext, Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';
import { SseRecorder } from '../helpers/SseRecorder';
import { unique } from '../helpers/accounts';
import type { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/provisioning';
import { signInContext } from '../helpers/session';

interface CommentEvent {
  event: string;
  commentId?: string;
  comment?: {
    id?: string;
    content?: string;
    author?: { displayName?: string };
    editable?: unknown;
  };
}

const topicFor = (imageId: string) => `comments/image/${imageId}`;

async function grantAndSubscribe(
  page: Page,
  imageId: string,
): Promise<SseRecorder> {
  await page.goto('/explore');

  const grant = await page.request.get(`/api/image/${imageId}/comments`);
  expect(grant.ok()).toBeTruthy();

  const sse = new SseRecorder(page);
  await sse.subscribe(topicFor(imageId));

  return sse;
}

async function signInNewUser(
  browser: Browser,
  label: string,
): Promise<{ api: ApiClient; context: BrowserContext }> {
  const account = unique(label);
  const api = await provisionUser(account);
  const context = await signInContext(browser, account);

  return { api, context };
}

test.describe('Comment SSE authorization', () => {
  test(
    'anonymous viewer without a grant gets 404 on comments and no SSE events',
    { tag: ['@anonymous', '@sc-893'] },
    async ({ page, browser }) => {
      const { api: owner, context } = await signInNewUser(browser, 'owner');

      try {
        const imageId = await owner.content.uploadImage({ isPublic: false });
        const controlPage = await context.newPage();

        await page.goto('/explore');

        const commentsResponse = await page.request.get(
          `/api/image/${imageId}/comments`,
        );
        expect(commentsResponse.status()).toBe(404);

        const anonymous = new SseRecorder(page);
        await anonymous.subscribe(topicFor(imageId));
        const control = await grantAndSubscribe(controlPage, imageId);

        const content = `Private note ${Date.now()}`;
        await owner.content.createComment(imageId, content);

        await control.waitForEvent<CommentEvent>(
          topicFor(imageId),
          (event) => event.event === 'comment_created',
        );
        expect(await anonymous.events(topicFor(imageId))).toEqual([]);
      } finally {
        await context.close();
      }
    },
  );

  test(
    'owner receives the created comment event',
    { tag: '@sc-893' },
    async ({ page, api }) => {
      const imageId = await api.content.uploadImage({ isPublic: false });
      const sse = await grantAndSubscribe(page, imageId);

      const content = `Owner comment ${Date.now()}`;
      await api.content.createComment(imageId, content);

      const event = await sse.waitForEvent<CommentEvent>(
        topicFor(imageId),
        (received) => received.event === 'comment_created',
      );
      expect(await sse.events(topicFor(imageId))).toHaveLength(1);
      expect(event.comment?.id).toBeTruthy();
      expect(event.comment?.content).toBe(content);
      expect(event.comment?.author).toBeTruthy();
      expect(event.comment).toHaveProperty('editable');
    },
  );

  test(
    'anonymous collection share viewer receives comment events',
    { tag: ['@anonymous', '@sc-893'] },
    async ({ page, actor }) => {
      const owner = await actor('owner');
      const imageId = await owner.content.uploadImage({ isPublic: false });
      const collectionId = await owner.content.createCollection({
        name: `Shared collection ${Date.now()}`,
      });
      await owner.content.addImageToCollection(collectionId, imageId);
      await owner.shares.publishCollectionShare(collectionId);

      await page.goto('/explore');

      await expect
        .poll(
          async () => {
            const response = await page.request.get(
              `/api/image/${imageId}/comments`,
            );
            return response.status();
          },
          { timeout: 15000 },
        )
        .toBe(200);

      const sse = new SseRecorder(page);
      await sse.subscribe(topicFor(imageId));

      const content = `Shared comment ${Date.now()}`;
      await owner.content.createComment(imageId, content);

      const event = await sse.waitForEvent<CommentEvent>(
        topicFor(imageId),
        (received) => received.event === 'comment_created',
      );
      expect(await sse.events(topicFor(imageId))).toHaveLength(1);
      expect(event.comment?.content).toBe(content);
    },
  );

  test(
    'owner receives the edited comment event',
    { tag: '@sc-893' },
    async ({ page, api }) => {
      const imageId = await api.content.uploadImage({ isPublic: false });
      const commentId = await api.content.createComment(
        imageId,
        `Before edit ${Date.now()}`,
      );
      const sse = await grantAndSubscribe(page, imageId);

      const edited = `After edit ${Date.now()}`;
      await api.content.editComment(commentId, edited);

      const event = await sse.waitForEvent<CommentEvent>(
        topicFor(imageId),
        (received) =>
          received.event === 'comment_edited' &&
          received.comment?.id === commentId,
      );
      expect(event.comment?.content).toBe(edited);
    },
  );

  test(
    'owner receives the deleted comment event without a comment payload',
    { tag: '@sc-893' },
    async ({ page, api }) => {
      const imageId = await api.content.uploadImage({ isPublic: false });
      const commentId = await api.content.createComment(
        imageId,
        `To delete ${Date.now()}`,
      );
      const sse = await grantAndSubscribe(page, imageId);

      await api.content.deleteComment(commentId);

      const event = await sse.waitForEvent<CommentEvent>(
        topicFor(imageId),
        (received) =>
          received.event === 'comment_deleted' &&
          received.commentId === commentId,
      );
      expect(event).not.toHaveProperty('comment');
    },
  );

  test(
    'logged-in nonOwner gets 404 on comments and receives no SSE events',
    { tag: '@sc-893' },
    async ({ page, api, browser }) => {
      const { context: nonOwnerContext } = await signInNewUser(
        browser,
        'nonowner',
      );

      try {
        const imageId = await api.content.uploadImage({ isPublic: false });
        const nonOwnerPage = await nonOwnerContext.newPage();
        await nonOwnerPage.goto('/explore');

        const denied = await nonOwnerPage.request.get(
          `/api/image/${imageId}/comments`,
        );
        expect(denied.status()).toBe(404);

        const nonOwner = new SseRecorder(nonOwnerPage);
        await nonOwner.subscribe(topicFor(imageId));
        const control = await grantAndSubscribe(page, imageId);

        const commentId = await api.content.createComment(
          imageId,
          `Hidden ${Date.now()}`,
        );
        await api.content.editComment(commentId, `Hidden edit ${Date.now()}`);

        await control.waitForEvent<CommentEvent>(
          topicFor(imageId),
          (event) => event.event === 'comment_created',
        );
        await control.waitForEvent<CommentEvent>(
          topicFor(imageId),
          (event) => event.event === 'comment_edited',
        );
        expect(await nonOwner.events(topicFor(imageId))).toEqual([]);
      } finally {
        await nonOwnerContext.close();
      }
    },
  );
});
