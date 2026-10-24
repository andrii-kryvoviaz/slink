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
  for (const viewer of [
    { label: 'anonymous grid', tag: '@anonymous', mode: 'grid' },
    { label: 'signed-in list', tag: '@authenticated', mode: 'list' },
  ]) {
    test(
      `renders public uploads and restores images made public again without reloading in ${viewer.label}`,
      { tag: viewer.tag },
      async ({ page, actor, explorePage, layoutControls }, testInfo) => {
        const owner = await actor('live-gallery');
        const existingId = await owner.content.uploadImage({ isPublic: true });
        await layoutControls.setSettingCookie(
          'explore',
          JSON.stringify({ viewMode: viewer.mode }),
        );
        await explorePage.goto();
        await expect(explorePage.mediaFor(existingId)).toBeVisible();
        const sse = new SseRecorder(page);
        await sse.subscribe(TOPIC);

        const imageId = await owner.content.uploadImage({ isPublic: true });
        const added = await sse.waitForEvent(TOPIC, isAdded(imageId));
        await testInfo.attach('image-added-event', {
          body: JSON.stringify(added, null, 2),
          contentType: 'application/json',
        });
        await expect(explorePage.mediaFor(imageId)).toBeVisible();
        await expect
          .poll(() =>
            explorePage
              .mediaFor(imageId)
              .evaluate((img: HTMLImageElement) => img.naturalWidth > 0),
          )
          .toBe(true);

        await owner.content.setMediaVisibility([imageId], false);
        await sse.waitForEvent(TOPIC, isRemoved(imageId));
        await expect(explorePage.mediaFor(imageId)).toHaveCount(0);

        await owner.content.setMediaVisibility([imageId], true);
        await expect(explorePage.mediaFor(imageId)).toBeVisible();
      },
    );
  }

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
