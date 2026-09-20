import { describe, expect, it } from 'vitest';

import { actionsMenuTriggerTheme } from './actions-menu.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('actionsMenuTriggerTheme', () => {
  it('draws the focus ring flush against the trigger', () => {
    expect(tokens(actionsMenuTriggerTheme())).toEqual(
      expect.arrayContaining([
        'focus-visible:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/30',
      ]),
    );
  });

  it('carries no ring offset, whose default colour painted white stripes in dark mode', () => {
    for (const tone of ['ghost', 'dark'] as const) {
      expect(
        tokens(actionsMenuTriggerTheme({ tone })).filter((token) =>
          token.includes('ring-offset'),
        ),
      ).toEqual([]);
    }
  });
});
