import { describe, expect, it } from 'vitest';

import { notificationTime } from './NotificationTime.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationTime', () => {
  it('renders the time as small muted text', () => {
    const time = tokens(notificationTime());

    expect(time).toEqual(
      expect.arrayContaining(['text-xs', 'text-foreground-muted']),
    );
  });

  it('keeps positioning classes passed by the consumer', () => {
    const time = tokens(notificationTime({ class: 'ml-auto' }));

    expect(time).toContain('ml-auto');
  });
});
