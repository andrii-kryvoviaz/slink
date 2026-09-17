import { expect, test } from '../fixtures/auth.fixture';
import { ExplorePage } from '../pages/ExplorePage';

const IMAGES_ENDPOINT = /\/api\/images(\?|$)/;

const malformedCookieCases: Array<{ label: string; value: string }> = [
  { label: 'not-json', value: 'not-json' },
  {
    label: 'unsupported viewMode',
    value: JSON.stringify({ viewMode: 'table' }),
  },
  { label: 'bogus viewMode', value: JSON.stringify({ viewMode: 'bogus' }) },
];

test.describe('Explore settings cookie fallback', () => {
  for (const { label, value } of malformedCookieCases) {
    test(`recovers from a ${label} settings.explore cookie`, async ({
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
          value,
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
  }
});
