import { describe, expect, it } from 'vitest';

import { notificationActorName } from './NotificationActorName.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationActorName', () => {
  it('renders the unread name in medium full-contrast text', () => {
    const name = tokens(notificationActorName());

    expect(name).toEqual(
      expect.arrayContaining(['font-medium', 'text-foreground']),
    );
    expect(name).not.toContain('text-foreground-soft');
    expect(name).not.toContain('text-foreground-muted');
  });

  it('dims the name to the row tone once read', () => {
    const name = tokens(notificationActorName({ read: true }));

    expect(name).toEqual(
      expect.arrayContaining(['font-medium', 'text-foreground-muted']),
    );
    expect(name).not.toContain('text-foreground');
    expect(name).not.toContain('text-foreground-soft');
  });

  it('keeps layout classes passed by the consumer', () => {
    const name = tokens(notificationActorName({ class: 'truncate' }));

    expect(name).toContain('truncate');
  });
});
