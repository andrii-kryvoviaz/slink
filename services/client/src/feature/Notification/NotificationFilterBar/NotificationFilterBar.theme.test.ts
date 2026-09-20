import { describe, expect, it } from 'vitest';

import { notificationFilterBarTheme } from './NotificationFilterBar.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationFilterBarTheme', () => {
  it('scrolls the chips horizontally on narrow screens', () => {
    const root = tokens(notificationFilterBarTheme().root());

    expect(root).toEqual(
      expect.arrayContaining(['overflow-x-auto', 'max-w-full']),
    );
  });

  it('pads the scroll container so the focus ring is not clipped', () => {
    const root = tokens(notificationFilterBarTheme().root());

    expect(root).toEqual(expect.arrayContaining(['p-1', '-m-1']));
  });

  it('keeps the bottom margin after the tailwind-merge pass', () => {
    const root = tokens(notificationFilterBarTheme().root());

    expect(root).toEqual(expect.arrayContaining(['mb-6']));
  });
});
