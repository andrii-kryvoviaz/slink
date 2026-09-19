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

  it('sets the name slot in medium weight', () => {
    const name = tokens(notificationActorList().name());

    expect(name).toEqual(expect.arrayContaining(['font-medium']));
  });
});
