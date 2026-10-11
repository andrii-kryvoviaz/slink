import { expect, test } from '../fixtures/auth.fixture';
import { captureListingRequests } from '../helpers/listingRequests';
import { CommentSection } from '../pages/CommentSection';

test.describe('Live comment UI', () => {
  test(
    'comment from another user appears in the open list without a refetch',
    { tag: '@sc-895' },
    async ({ actor, explorePage }) => {
      const owner = await actor('owner');
      const commenter = await actor('commenter');
      const imageId = await owner.content.uploadImage({ isPublic: true });
      const page = explorePage.page;
      const topic = `comments/image/${imageId}`;
      const commentsPath = `/api/image/${imageId}/comments`;

      const subscribed = page.waitForResponse(
        (response) =>
          new URL(response.url()).pathname === '/sse' &&
          new URL(response.url()).searchParams.get('topic') === topic,
      );

      await page.goto(`/explore?post=${imageId}`);
      await expect(explorePage.viewer).toBeVisible();

      const comments = new CommentSection(page, imageId);
      await comments.waitForLoaded();

      expect((await subscribed).status()).toBe(200);

      const listRequests = captureListingRequests(page, commentsPath);
      const content = `Live comment ${Date.now()}`;

      await commenter.content.createComment(imageId, content);

      await expect(comments.row(content)).toBeVisible();
      expect(listRequests).toHaveLength(0);
    },
  );
});
