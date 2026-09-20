import { describe, expect, it } from 'vitest';

import { buttonGroupItemVariants } from './button-group.svelte';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const variants = [
  'default',
  'primary',
  'primary-outline',
  'secondary',
  'ghost',
  'destructive',
] as const;

describe('buttonGroupItemVariants', () => {
  it('draws the house focus ring flush against the item', () => {
    expect(tokens(buttonGroupItemVariants())).toEqual(
      expect.arrayContaining([
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
      ]),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      expect(
        tokens(buttonGroupItemVariants({ variant })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
