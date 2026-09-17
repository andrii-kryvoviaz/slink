import { expect, test } from '../fixtures/auth.fixture';
import { ExplorePage } from '../pages/ExplorePage';

const IMAGES_ENDPOINT = /\/api\/images(\?|$)/;

test.describe('Explore settings cookie fallback', () => {
  test('recovers from a malformed settings.explore cookie', async ({
    explorePage,
    layoutControls,
    actor,
    page,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await page.context().addCookies([
      {
        name: 'settings.explore',
        value: 'not-json',
        url: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
      },
    ]);

    let release: () => void = () => {};
    const held = new Promise<void>((resolve) => {
      release = resolve;
    });

    await page.route(IMAGES_ENDPOINT, async (route) => {
      await held;
      await route.continue();
    });

    const strayControl = page.locator(
      'main [role="listbox"], main [aria-haspopup="listbox"]',
    );
    const gridRadio = page.getByRole('radio', { name: 'Grid' });
    const listRadio = page.getByRole('radio', { name: 'List' });

    await page.goto(ExplorePage.URL);

    await expect(gridRadio).toHaveAttribute('aria-checked', 'true');
    await expect(listRadio).toHaveAttribute('aria-checked', 'false');
    await expect(strayControl).toHaveCount(0);

    release();

    await expect(explorePage.cardFor(imageId)).toBeVisible();
    await expect(strayControl).toHaveCount(0);

    await listRadio.click();
    await expect(listRadio).toHaveAttribute('aria-checked', 'true');
    await expect(gridRadio).toHaveAttribute('aria-checked', 'false');

    await expect
      .poll(() => layoutControls.readSettingCookie('explore'))
      .toBe(JSON.stringify({ viewMode: 'list' }));
  });
});
