import { describe, expect, it } from 'vitest';

import { buttonVariants } from './button.svelte';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const ringWidth = (tokens: string[]) =>
  tokens.filter((token) =>
    /^(focus|focus-visible):ring-(\[|[1-9])/.test(token),
  );

const variants = [
  'default',
  'primary',
  'secondary',
  'dark',
  'cta',
  'invisible',
  'outline',
  'link',
  'danger',
  'modern',
  'glass',
  'glass-dark',
  'primary-dark',
  'soft-violet',
  'soft-accent',
  'soft-blue',
  'soft-green',
  'soft-red',
  'soft-amber',
  'outline-blue',
  'outline-green',
  'outline-accent',
  'outline-amber',
  'outline-danger',
  'destructive',
  'ghost',
  'transparent',
  'toggle',
] as const;

describe('buttonVariants', () => {
  it('draws the house focus ring flush against the control', () => {
    expect(tokens(buttonVariants())).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
      ]),
    );
  });

  it('never lets a variant override the ring width away to zero', () => {
    for (const variant of variants) {
      expect(ringWidth(tokens(buttonVariants({ variant })))).not.toEqual([]);
    }
  });

  it('lets a variant recolour the ring without dropping its width', () => {
    const destructive = tokens(buttonVariants({ variant: 'destructive' }));

    expect(destructive).toEqual(
      expect.arrayContaining([
        'focus-visible:ring-2',
        'focus-visible:ring-danger/20',
        'dark:focus-visible:ring-danger/40',
      ]),
    );
  });

  it('carries no ring offset on any variant, whose default colour painted white stripes in dark mode', () => {
    for (const variant of variants) {
      expect(
        tokens(buttonVariants({ variant })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
