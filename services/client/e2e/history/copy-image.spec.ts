import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('History copy image content', () => {
  test('copies the image bytes of a private image for its owner', async ({
    api,
    page,
    historyPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    const publicEndpointFailures: string[] = [];
    page.on('response', (response) => {
      if (
        response.status() === 404 &&
        response.url().includes('/api/image/public/')
      ) {
        publicEndpointFailures.push(response.url());
      }
    });

    await historyPage.useGridView();
    await historyPage.goto();

    await historyPage.copyImageContent(imageId);

    await expect(async () => {
      expect(await historyPage.clipboardImageTypes()).toContain('image/png');
    }).toPass({ timeout: 15000 });

    expect(publicEndpointFailures).toEqual([]);
  });
});
