import { threadBlock } from '@slink/ui/components/thread-block/thread-block.theme';
import { describe, expect, it } from 'vitest';

import { notificationActorName } from './NotificationActorName.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationActorName', () => {
  it('renders the name in medium full-contrast text', () => {
    const name = tokens(notificationActorName());

    expect(name).toEqual(
      expect.arrayContaining(['font-medium', 'text-foreground']),
    );
    expect(name).not.toContain('text-foreground-soft');
    expect(name).not.toContain('text-foreground-muted');
  });

  it('keeps one tone whether the group is read or not', () => {
    expect(notificationActorName.variantKeys).toEqual([]);
  });

  it('stays above the comment row tone in both states', () => {
    expect(tokens(threadBlock().row())).toContain('text-foreground-muted');
    expect(tokens(notificationActorName())).not.toContain(
      'text-foreground-muted',
    );
  });

  it('keeps layout classes passed by the consumer', () => {
    const name = tokens(notificationActorName({ class: 'truncate' }));

    expect(name).toContain('truncate');
  });
});
