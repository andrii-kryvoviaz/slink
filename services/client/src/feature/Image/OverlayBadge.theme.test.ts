import { describe, expect, it } from 'vitest';

import {
  overlayBadgeContainerTheme,
  overlayBadgeValueTheme,
} from './OverlayBadge.theme';
import { viewCountBadgeContainerTheme } from './ViewCountBadge/ViewCountBadge.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('overlayBadgeContainerTheme', () => {
  it('keeps the chip on a single line in every variant', () => {
    expect(
      tokens(overlayBadgeContainerTheme({ variant: 'overlay' })),
    ).toContain('whitespace-nowrap');
    expect(
      tokens(overlayBadgeContainerTheme({ variant: 'compact' })),
    ).toContain('whitespace-nowrap');
  });

  it('defaults to the overlay variant', () => {
    expect(tokens(overlayBadgeContainerTheme())).toEqual(
      tokens(overlayBadgeContainerTheme({ variant: 'overlay' })),
    );
  });
});

describe('overlayBadgeValueTheme', () => {
  it('renders the value as small soft text on an overlay', () => {
    expect(tokens(overlayBadgeValueTheme({ variant: 'overlay' }))).toEqual(
      expect.arrayContaining(['text-[11px]', 'text-foreground-soft']),
    );
  });
});

describe('viewCountBadgeContainerTheme', () => {
  it('keeps the chip on a single line in every variant', () => {
    for (const variant of ['card', 'compact', 'overlay', 'badge'] as const) {
      expect(tokens(viewCountBadgeContainerTheme({ variant }))).toContain(
        'whitespace-nowrap',
      );
    }
  });

  it('shares the overlay chip surface with the other badges', () => {
    expect(
      tokens(viewCountBadgeContainerTheme({ variant: 'overlay' })),
    ).toEqual(tokens(overlayBadgeContainerTheme({ variant: 'overlay' })));
  });
});
