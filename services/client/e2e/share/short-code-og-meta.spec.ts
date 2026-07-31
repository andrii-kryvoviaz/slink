import { expect, test } from '../fixtures/auth.fixture';

const DISCORD_BOT_USER_AGENT =
  'Mozilla/5.0 (compatible; Discordbot/2.0; +https://discordapp.com)';

test.describe('Short code OG meta', { tag: '@serial' }, () => {
  test.use({ userAgent: DISCORD_BOT_USER_AGENT });

  test('serves a fetchable og:image to a crawler when guest browsing is disabled', async ({
    page,
    api,
    settingsApi,
  }) => {
    await settingsApi.set('access', {
      allowUnauthenticatedAccess: false,
      requireAuthForMediaShares: false,
    });

    const imageId = await api.content.uploadImage({ isPublic: true });
    const share = await api.shares.publishImageShare(imageId);
    const code = api.shares.getShortCode(share);
    expect(code).not.toBeNull();

    await page.goto(`/i/${code}`);

    const ogImage = await page
      .locator('meta[property="og:image"]')
      .getAttribute('content');

    expect(ogImage).toBeTruthy();
    expect(ogImage).toMatch(/^https?:\/\//);

    const response = await page.request.get(ogImage!);
    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('image');
  });
});
