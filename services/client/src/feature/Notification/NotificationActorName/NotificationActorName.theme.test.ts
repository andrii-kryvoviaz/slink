import { describe, expect, it } from 'vitest';

import { notificationActorName } from './NotificationActorName.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationActorName', () => {
  it('renders the name in medium soft text while unread', () => {
    const name = tokens(notificationActorName());

    expect(name).toEqual(
      expect.arrayContaining(['font-medium', 'text-foreground-soft']),
    );
    expect(name).not.toContain('text-foreground-muted');
    expect(name).not.toContain('text-foreground');
  });

  it('dims the name to muted text once read', () => {
    const name = tokens(notificationActorName({ read: true }));

    expect(name).toEqual(
      expect.arrayContaining(['font-medium', 'text-foreground-muted']),
    );
    expect(name).not.toContain('text-foreground-soft');
    expect(name).not.toContain('text-foreground');
  });

  it('keeps layout classes passed by the consumer', () => {
    const name = tokens(notificationActorName({ class: 'truncate' }));

    expect(name).toContain('truncate');
  });
});
