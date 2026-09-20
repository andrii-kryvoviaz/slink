import { notificationActorName } from '@slink/feature/Notification/NotificationActorName/NotificationActorName.theme';
import { describe, expect, it } from 'vitest';

import { notificationActorList } from './NotificationActorList.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationActorList', () => {
  it('keeps the time slot to layout and leaves its tone to the shared time', () => {
    const time = tokens(notificationActorList().time());

    expect(time).toEqual(expect.arrayContaining(['ml-auto', 'shrink-0']));
    expect(time).not.toContain('text-xs');
    expect(time).not.toContain('text-foreground-muted');
  });

  it('keeps the name slot to layout and leaves its tone to the actor name', () => {
    const name = tokens(notificationActorList().name());

    expect(name).toEqual(expect.arrayContaining(['min-w-0', 'truncate']));
    expect(name).not.toContain('font-medium');
    expect(name).not.toContain('text-foreground');
    expect(name).not.toContain('text-foreground-soft');
    expect(name).not.toContain('text-foreground-muted');
  });

  it('renders an actor at full contrast whether the group is read or not', () => {
    const name = tokens(
      notificationActorName({ class: notificationActorList().name() }),
    );

    expect(name).toEqual(
      expect.arrayContaining(['truncate', 'font-medium', 'text-foreground']),
    );
    expect(name).not.toContain('text-foreground-muted');
    expect(notificationActorName.variantKeys).toEqual([]);
  });
});
