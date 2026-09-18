import { expect, test } from '../fixtures/auth.fixture';
import { ssrViewModeChecked, stripSsrComments } from '../helpers/ssr';
import { ExplorePage } from '../pages/ExplorePage';

const IMAGES_ENDPOINT = /\/api\/images(\?|$)/;

const malformedCookieCases: Array<{ label: string; value: string }> = [
  { label: 'not-json', value: 'not-json' },
  { label: 'empty object', value: '{}' },
  { label: 'null literal', value: 'null' },
  { label: 'array literal', value: '[]' },
  { label: 'JSON string "list"', value: JSON.stringify('list') },
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

      await layoutControls.setSettingCookie('explore', value);

      const html = await (await page.request.get(ExplorePage.URL)).text();
      const stripped = stripSsrComments(html);
      expect(stripped).not.toContain('aria-haspopup="listbox"');
      expect(stripped).not.toContain('role="listbox"');
      expect(ssrViewModeChecked(stripped, 'Grid')).toBe(true);
      expect(ssrViewModeChecked(stripped, 'List')).toBe(false);

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
