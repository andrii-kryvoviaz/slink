import { describe, expect, it } from 'vitest';

import { ModeSwitchTheme } from './ModeSwitch.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const variants = ['default', 'minimal', 'glass', 'floating', 'pill'] as const;

describe('ModeSwitchTheme', () => {
  it('draws the focus ring flush against the round switch', () => {
    expect(tokens(ModeSwitchTheme())).toEqual(
      expect.arrayContaining([
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/20',
      ]),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      expect(
        tokens(ModeSwitchTheme({ variant })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
