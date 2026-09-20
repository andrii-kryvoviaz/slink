import { describe, expect, it } from 'vitest';

import { TabMenuItemTheme } from './TabMenu.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const variants = ['default', 'minimal', 'pills', 'underline'] as const;

describe('TabMenuItemTheme', () => {
  it('draws the focus ring flush against the tab', () => {
    expect(tokens(TabMenuItemTheme())).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-accent/20',
      ]),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      for (const active of [true, false]) {
        expect(
          tokens(TabMenuItemTheme({ variant, active })).filter((token) =>
            token.includes('ring-offset'),
          ),
        ).toEqual([]);
      }
    }
  });
});
