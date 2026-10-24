import { execFileSync } from 'child_process';

import { expect, test } from '../fixtures/auth.fixture';
import { SseRecorder } from '../helpers/SseRecorder';

const TOPIC = 'public-feed';
const WORKER = '/run/service/feed';

function inContainer(...args: string[]): string {
  return execFileSync(
    'docker',
    ['compose', '-p', 'slink-e2e', 'exec', '-T', 'slink', ...args],
    { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] },
  ).trim();
}

function workerPid(): number {
  return Number(inContainer('s6-svstat', '-o', 'pid', WORKER));
}

test.describe('Bundled public feed worker', { tag: '@serial' }, () => {
  test('automatically restarts after an abrupt exit and delivers public uploads', async ({
    page,
    actor,
  }) => {
    const owner = await actor('worker-crash');
    await page.goto('/explore');
    const sse = new SseRecorder(page);
    await sse.subscribe(TOPIC);

    const originalPid = workerPid();
    expect(originalPid).toBeGreaterThan(0);
    inContainer('s6-svc', '-k', WORKER);

    await expect
      .poll(() => {
        const pid = workerPid();
        return pid > 0 && pid !== originalPid;
      })
      .toBe(true);

    const imageId = await owner.content.uploadImage({ isPublic: true });
    await sse.waitForEvent(
      TOPIC,
      (event) => event.event === 'image_added' && event.image?.id === imageId,
    );
  });

  test('recovers committed changes after downtime without publishing private or deleted metadata', async ({
    page,
    actor,
  }) => {
    const owner = await actor('worker-backlog');
    await page.goto('/explore');
    const sse = new SseRecorder(page);
    await sse.subscribe(TOPIC);
    inContainer('s6-svc', '-d', WORKER);

    try {
      await expect.poll(workerPid).toBe(-1);
      const privateId = await owner.content.uploadImage({ isPublic: true });
      await owner.content.setMediaVisibility([privateId], false);
      const deletedId = await owner.content.uploadImage({ isPublic: true });
      await owner.content.deleteImage(deletedId);
      const publicId = await owner.content.uploadImage({ isPublic: true });
      const description = `Recovered current description ${Date.now()}`;
      await owner.content.updateImage(publicId, { description });

      inContainer('s6-svc', '-u', WORKER);

      for (const imageId of [privateId, deletedId]) {
        await sse.waitForEvent(
          TOPIC,
          (event) =>
            event.event === 'image_removed' && event.imageId === imageId,
        );
      }
      const recovered = await sse.waitForEvent(
        TOPIC,
        (event) =>
          event.event === 'image_added' && event.image?.id === publicId,
      );
      expect(recovered.image.attributes.description).toBe(description);
      expect(
        (await sse.events(TOPIC)).filter((event) =>
          [privateId, deletedId].includes(event.image?.id),
        ),
      ).toEqual([]);
    } finally {
      inContainer('s6-svc', '-u', WORKER);
    }
  });
});
