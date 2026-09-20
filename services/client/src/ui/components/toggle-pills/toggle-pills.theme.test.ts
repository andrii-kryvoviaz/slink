import { describe, expect, it } from 'vitest';

import { togglePillsItemTheme } from './toggle-pills.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('togglePillsItemTheme', () => {
  it('draws the house focus ring flush against the pill border', () => {
    expect(tokens(togglePillsItemTheme())).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
      ]),
    );
  });

  it('carries no ring offset, whose default colour painted white stripes in dark mode', () => {
    for (const size of ['sm', 'md'] as const) {
      expect(
        tokens(togglePillsItemTheme({ size })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
