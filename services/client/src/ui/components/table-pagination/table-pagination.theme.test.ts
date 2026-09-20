import { describe, expect, it } from 'vitest';

import { tablePaginationPageButtonTheme } from './table-pagination.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('tablePaginationPageButtonTheme', () => {
  it('draws the house focus ring flush against the page button', () => {
    expect(
      tokens(tablePaginationPageButtonTheme({ status: 'interactive' })),
    ).toEqual(
      expect.arrayContaining([
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-ring/50',
      ]),
    );
  });

  it('carries no ring offset, whose default colour painted white stripes in dark mode', () => {
    for (const variant of ['default', 'neutral'] as const) {
      for (const status of ['interactive', 'static'] as const) {
        expect(
          tokens(tablePaginationPageButtonTheme({ variant, status })).filter(
            (token) => token.includes('ring-offset'),
          ),
        ).toEqual([]);
      }
    }
  });
});
