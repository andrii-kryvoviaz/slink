import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

import { expect, test } from '../fixtures/auth.fixture';

const THEME_DIR = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  '../../src/theme',
);

const TOKEN_FILES = ['presets', 'core'].flatMap((group) =>
  fs
    .readdirSync(path.join(THEME_DIR, group))
    .map((entry) => path.join(THEME_DIR, group, entry)),
);

const MODES = ['light', 'dark'] as const;

const VIEWPORT_WIDTH = 1600;

function contractTokens(): string[] {
  const source = TOKEN_FILES.map((file) => fs.readFileSync(file, 'utf-8')).join(
    '\n',
  );

  const names = [...source.matchAll(/(?:^|;)\s*(--[\w-]+)\s*:/gm)].map(
    (match) => match[1],
  );

  return [...new Set(names)];
}

test.describe('Token contract preview', () => {
  for (const mode of MODES) {
    test(`renders every contract token in ${mode} mode`, async ({ page }) => {
      await page.setViewportSize({ width: VIEWPORT_WIDTH, height: 1000 });
      await page.goto(`/dev/tokens?mode=${mode}`);

      const preview = page.getByTestId('token-preview');
      const pane = page.getByTestId(`token-pane-${mode}`);
      await expect(pane).toBeVisible();

      const rendered = await pane.locator('[data-token]').evaluateAll((nodes) =>
        nodes.map((node) => ({
          token: node.getAttribute('data-token'),
          value: node.querySelectorAll('code')[1]?.textContent?.trim() ?? '',
        })),
      );

      const renderedTokens = rendered.map((entry) => entry.token);
      const missing = contractTokens().filter(
        (token) => !renderedTokens.includes(token),
      );

      expect(missing).toEqual([]);
      expect(rendered.filter((entry) => !entry.value)).toEqual([]);

      const box = await preview.boundingBox();
      await page.setViewportSize({
        width: VIEWPORT_WIDTH,
        height: Math.ceil(box?.height ?? 1000) + 200,
      });

      await expect(preview).toHaveScreenshot(`tokens-${mode}.png`, {
        animations: 'disabled',
        caret: 'hide',
      });
    });
  }
});
