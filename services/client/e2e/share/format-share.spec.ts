import { type Response } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

import { expect, test } from '../fixtures/auth.fixture';
import { createUniqueGif } from '../helpers/uniqueImage';

const FIXTURES_DIR = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  '../fixtures',
);
const ANIMATED_GIF = path.join(FIXTURES_DIR, 'animated.gif');
const SAMPLE_HEIC = path.join(FIXTURES_DIR, 'sample.heic');

function countGifFrames(bytes: Buffer): number {
  let frames = 0;
  for (let i = 0; i < bytes.length - 1; i++) {
    const isGraphicControl = bytes[i] === 0x21 && bytes[i + 1] === 0xf9;
    const isImageDescriptor = bytes[i] === 0x2c;
    if (isGraphicControl || isImageDescriptor) {
      frames++;
    }
  }
  return frames;
}

async function imageIdFromCompletion(completion: Response): Promise<string> {
  const payload = await completion.json();
  const duplicateId = payload?.error?.violations?.find(
    (violation: { property?: string; data?: { imageId?: string } }) =>
      violation.property === 'duplicate_image',
  )?.data?.imageId;

  return String(payload?.data?.id ?? payload?.id ?? duplicateId);
}

test.describe('Upload to share to manipulation', () => {
  test('keeps an animated GIF animated when served with a width', async ({
    uploadPage,
    page,
    api,
  }) => {
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    const gif = createUniqueGif(ANIMATED_GIF);
    const completion = await uploadPage.uploadBuffer({
      name: gif.name,
      mimeType: 'image/gif',
      buffer: gif.buffer,
    });

    const imageId = await imageIdFromCompletion(completion);
    expect(imageId).toBeTruthy();

    const detail = await api.content.getImageDetail(imageId);
    expect(detail.mimeType).toContain('gif');

    const servedUrl = `/image/${detail.fileName}?width=16`;
    const response = await page.request.get(servedUrl);

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('image/gif');

    const bytes = await response.body();
    expect(countGifFrames(bytes)).toBeGreaterThan(1);
  });

  test('force converts an uploaded HEIC to JPEG', async ({
    uploadPage,
    page,
    api,
  }) => {
    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    const completion = await uploadPage.uploadFixtureFile(
      SAMPLE_HEIC,
      'image/heic',
    );

    const imageId = await imageIdFromCompletion(completion);
    expect(imageId).toBeTruthy();

    const detail = await api.content.getImageDetail(imageId);
    expect(detail.mimeType).toContain('jpeg');
    expect(detail.fileName).toMatch(/\.(jpe?g)$/i);

    const response = await page.request.get(`/image/${detail.fileName}`);
    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('image/jpeg');
  });
});
