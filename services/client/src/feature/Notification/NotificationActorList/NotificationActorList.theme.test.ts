import { describe, expect, it } from 'vitest';

import { notificationActorList } from './NotificationActorList.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationActorList', () => {
  it('mutes and right-aligns the time slot', () => {
    const time = tokens(notificationActorList().time());

    expect(time).toEqual(
      expect.arrayContaining(['text-xs', 'text-foreground-muted', 'ml-auto']),
    );
  });

  it('keeps the name slot to layout and leaves its tone to the actor name', () => {
    const name = tokens(notificationActorList().name());

    expect(name).toEqual(expect.arrayContaining(['min-w-0', 'truncate']));
    expect(name).not.toContain('font-medium');
    expect(name).not.toContain('text-foreground');
    expect(name).not.toContain('text-foreground-soft');
    expect(name).not.toContain('text-foreground-muted');
  });
});
