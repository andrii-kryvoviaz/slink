import { describe, expect, it } from 'vitest';

import { BadgeTheme } from './Badge.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const variants = [
  'default',
  'blue',
  'emerald',
  'slate',
  'purple',
  'amber',
  'orange',
  'red',
  'success',
  'destructive',
  'warning',
  'info',
  'indigo',
  'pink',
  'neutral',
  'gradient',
  'neon',
  'minimal',
  'glass',
] as const;

describe('BadgeTheme', () => {
  it('draws the focus ring flush against the badge border', () => {
    expect(tokens(BadgeTheme())).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
      ]),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      expect(
        tokens(BadgeTheme({ variant })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
