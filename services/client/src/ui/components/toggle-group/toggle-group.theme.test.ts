import { describe, expect, it } from 'vitest';

import {
  toggleGroupInnerTheme,
  toggleGroupItemTheme,
} from './toggle-group.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('toggleGroupItemTheme', () => {
  it('draws the house focus ring inset so the clipping wrapper cannot cut it', () => {
    const item = tokens(toggleGroupItemTheme());

    expect(item).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-inset',
        'focus-visible:ring-ring/50',
      ]),
    );
  });

  it('carries no ring offset, whose default colour painted white stripes in dark mode', () => {
    expect(tokens(toggleGroupInnerTheme())).toContain('overflow-hidden');

    for (const variant of ['active', 'inactive'] as const) {
      for (const size of ['sm', 'md', 'lg'] as const) {
        expect(
          tokens(toggleGroupItemTheme({ variant, size })).filter((token) =>
            token.includes('ring-offset'),
          ),
        ).toEqual([]);
      }
    }
  });
});
