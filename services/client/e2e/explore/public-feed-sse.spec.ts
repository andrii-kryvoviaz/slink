import { expect, test } from '../fixtures/auth.fixture';
import { type SseEvent, SseRecorder } from '../helpers/SseRecorder';

const TOPIC = 'public-feed';

const mentions =
  (...imageIds: string[]) =>
  (event: SseEvent) =>
    imageIds.includes(event.image?.id) || imageIds.includes(event.imageId);

const isAdded = (imageId: string) => (event: SseEvent) =>
  event.event === 'image_added' && event.image?.id === imageId;

const isRemoved = (imageId: string) => (event: SseEvent) =>
  event.event === 'image_removed' && event.imageId === imageId;

test.describe('Public feed SSE', () => {
  test(
    'anonymous subscriber receives public uploads and never non-public changes',
    { tag: ['@anonymous', '@sc-894'] },
    async ({ page, actor }) => {
      const owner = await actor('owner');
      await page.goto('/explore');

      const sse = new SseRecorder(page);
      await sse.subscribe(TOPIC);

      const hiddenId = await owner.content.uploadImage({ isPublic: false });
      await owner.content.updateImage(hiddenId, {
        description: `Hidden edit ${Date.now()}`,
      });
      const deletedId = await owner.content.uploadImage({ isPublic: false });
      await owner.content.deleteImage(deletedId);

      const markerId = await owner.content.uploadImage({ isPublic: true });
      const added = await sse.waitForEvent(TOPIC, isAdded(markerId));
      expect(added.image.id).toBe(markerId);

      const leaked = (await sse.events(TOPIC)).filter(
        mentions(hiddenId, deletedId),
      );
      expect(leaked).toEqual([]);
    },
  );

  test(
    'going private emits image_removed and then stays silent',
    { tag: ['@anonymous', '@sc-894'] },
    async ({ page, actor }) => {
      const owner = await actor('owner');
      await page.goto('/explore');

      const sse = new SseRecorder(page);
      await sse.subscribe(TOPIC);

      const imageId = await owner.content.uploadImage({ isPublic: true });
      await sse.waitForEvent(TOPIC, isAdded(imageId));

      await owner.content.setMediaVisibility([imageId], false);
      const removed = await sse.waitForEvent(TOPIC, isRemoved(imageId));
      expect(removed).toEqual({ event: 'image_removed', imageId });

      await owner.content.updateImage(imageId, {
        description: `Private edit ${Date.now()}`,
      });
      const markerId = await owner.content.uploadImage({ isPublic: true });
      await sse.waitForEvent(TOPIC, isAdded(markerId));

      const events = await sse.events(TOPIC);
      const removedIndex = events.findIndex(isRemoved(imageId));
      const after = events.slice(removedIndex + 1).filter(mentions(imageId));
      expect(after).toEqual([]);
    },
  );
});
