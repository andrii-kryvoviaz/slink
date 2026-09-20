import { notificationActorName } from '@slink/feature/Notification/NotificationActorName/NotificationActorName.theme';
import { threadBlock } from '@slink/ui/components/thread-block/thread-block.theme';
import { describe, expect, it } from 'vitest';

import { notificationThread } from './NotificationThread.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

describe('notificationThread', () => {
  it('strips the hashtag chip vertical padding in the text slot', () => {
    const text = tokens(notificationThread().text());

    expect(text).toEqual(expect.arrayContaining(['[&_[data-hashtag]]:py-0']));
  });

  it('wraps the inline flow without breaking ordinary words', () => {
    const theme = notificationThread();
    const slots = [
      theme.row(),
      theme.flow(),
      theme.target(),
      theme.text(),
    ].flatMap(tokens);

    expect(tokens(theme.flow())).toEqual(
      expect.arrayContaining(['wrap-break-word', 'min-w-0']),
    );
    expect(slots).not.toContain('wrap-anywhere');
    expect(tokens(theme.target())).not.toContain('shrink-0');
  });

  it('keeps hashtag chips whole and truncates them within the line', () => {
    expect(tokens(notificationThread().text())).toEqual(
      expect.arrayContaining([
        '[&_[data-hashtag]]:inline-block',
        '[&_[data-hashtag]]:whitespace-nowrap',
        '[&_[data-hashtag]]:max-w-full',
        '[&_[data-hashtag]]:truncate',
      ]),
    );
  });

  it('aligns the row time to the first text line', () => {
    expect(tokens(notificationThread().row())).toEqual(
      expect.arrayContaining(['grid', 'items-baseline']),
    );
  });

  it('keeps comment text at the muted row tone while the name lifts above it in both states', () => {
    const theme = notificationThread();
    const ownSlots = [
      theme.row(),
      theme.flow(),
      theme.target(),
      theme.hiddenAuthor(),
      theme.text(),
      theme.toggleLabel(),
    ].flatMap(tokens);

    expect(
      ownSlots.filter((token) =>
        /(^|:)(text-foreground|font-medium)/.test(token),
      ),
    ).toEqual([]);
    expect(tokens(threadBlock().row())).toContain('text-foreground-muted');
    expect(tokens(notificationActorName())).toContain('text-foreground');
    expect(tokens(notificationActorName())).not.toContain(
      'text-foreground-muted',
    );
    expect(notificationActorName.variantKeys).toEqual([]);
  });

  it('owns no time class of its own', () => {
    expect(Object.keys(notificationThread())).not.toContain('time');
  });

  it('hides a repeated author name visually only', () => {
    expect(tokens(notificationThread().hiddenAuthor())).toEqual(['sr-only']);
  });

  it('renders the quoted parent as one dim, unweighted line', () => {
    const quote = tokens(notificationThread().quote());

    expect(quote).toEqual(
      expect.arrayContaining(['truncate', 'text-xs', 'text-foreground-subtle']),
    );
    expect(quote).not.toContain('font-medium');
  });

  it('sits one step dimmer than the comment text tone', () => {
    const quote = tokens(notificationThread().quote());

    expect(quote).not.toContain('text-foreground-muted');
    expect(tokens(threadBlock().row())).toContain('text-foreground-muted');
  });

  it('leaves the quote inset to the thread rail', () => {
    const quote = tokens(notificationThread().quote());

    expect(quote.filter((token) => /(^|:)p[ltrbxy]?-/.test(token))).toEqual([]);
  });
});
