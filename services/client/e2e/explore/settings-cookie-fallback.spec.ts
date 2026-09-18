import { expect, test } from '../fixtures/auth.fixture';
import { ssrViewModeChecked, stripSsrComments } from '../helpers/ssr';
import { ExplorePage } from '../pages/ExplorePage';

const IMAGES_ENDPOINT = /\/api\/images(\?|$)/;

const lightCookieCases: Array<{ label: string; value: string }> = [
  { label: 'not-json', value: 'not-json' },
  { label: 'empty object', value: '{}' },
  { label: 'null literal', value: 'null' },
  { label: 'array literal', value: '[]' },
  { label: 'JSON string "list"', value: JSON.stringify('list') },
  { label: 'bogus viewMode', value: JSON.stringify({ viewMode: 'bogus' }) },
];

test.describe('Explore settings cookie fallback', () => {
  test("recovers from an unsupported viewMode settings.explore cookie while listing another user's public image", async ({
    explorePage,
    layoutControls,
    actor,
    page,
  }) => {
    const publicImageOwner = await actor('publicImageOwner');
    const imageId = await publicImageOwner.content.uploadImage({
      isPublic: true,
    });

    await layoutControls.setSettingCookie(
      'explore',
      JSON.stringify({ viewMode: 'table' }),
    );

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

    const strayControl = explorePage.strayViewModeListbox;
    const gridRadio = explorePage.viewModeOption('Grid');
    const listRadio = explorePage.viewModeOption('List');

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

  for (const { label, value } of lightCookieCases) {
    test(`falls back to Grid on SSR and after hydration for a ${label} settings.explore cookie`, async ({
      explorePage,
      layoutControls,
      api,
      page,
    }) => {
      const imageId = await api.content.uploadImage({ isPublic: true });

      await layoutControls.setSettingCookie('explore', value);

      const html = await (await page.request.get(ExplorePage.URL)).text();
      const stripped = stripSsrComments(html);
      expect(stripped).not.toContain('aria-haspopup="listbox"');
      expect(stripped).not.toContain('role="listbox"');
      expect(ssrViewModeChecked(stripped, 'Grid')).toBe(true);
      expect(ssrViewModeChecked(stripped, 'List')).toBe(false);

      await page.goto(ExplorePage.URL);

      await expect(explorePage.cardFor(imageId)).toBeVisible();
      await expect(explorePage.viewModeOption('Grid')).toHaveAttribute(
        'aria-checked',
        'true',
      );
      await expect(explorePage.viewModeOption('List')).toHaveAttribute(
        'aria-checked',
        'false',
      );
      await expect(explorePage.strayViewModeListbox).toHaveCount(0);
    });
  }
});
