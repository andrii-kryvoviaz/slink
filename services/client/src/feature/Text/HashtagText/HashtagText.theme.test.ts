import { describe, expect, it } from 'vitest';

import { hashtagVariants } from './HashtagText.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const variants = [
  'default',
  'primary',
  'secondary',
  'success',
  'warning',
  'danger',
  'minimal',
  'glass',
] as const;

describe('hashtagVariants', () => {
  it('draws the focus ring flush against the hashtag border', () => {
    expect(tokens(hashtagVariants())).toEqual(
      expect.arrayContaining(['focus:outline-none', 'focus:ring-2']),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      expect(
        tokens(hashtagVariants({ variant })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
