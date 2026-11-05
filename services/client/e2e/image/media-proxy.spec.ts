import { expect, test } from '../fixtures/auth.fixture';

test.describe('Image proxy route', () => {
  test('does not expose authenticated API JSON through path traversal', async ({
    page,
  }) => {
    const response = await page.request.get('/image/..%2Fuser');

    expect(response.status()).toBe(404);
    expect(response.headers()['access-control-allow-origin']).toBeUndefined();
  });

  test('does not expose image sub-resources under the image path', async ({
    page,
    api,
  }) => {
    const imageId = await api.content.uploadImage();

    const response = await page.request.get(`/image/${imageId}/detail`);

    expect(response.status()).toBe(404);
    expect(response.headers()['access-control-allow-origin']).toBeUndefined();
  });

  test('serves an owned image with cross-origin access', async ({
    page,
    api,
  }) => {
    const imageId = await api.content.uploadImage();
    const detail = await api.content.getImageDetail(imageId);

    const response = await page.request.get(`/image/${detail.fileName}`);

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toMatch(/^image\//);
    expect(response.headers()['access-control-allow-origin']).toBe('*');
  });
});
