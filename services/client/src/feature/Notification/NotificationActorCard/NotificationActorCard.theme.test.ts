import { describe, expect, it } from 'vitest';

import { notificationEntry } from '../NotificationEntry/NotificationEntry.theme';
import { notificationActorCard } from './NotificationActorCard.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const SURFACE_TOKEN = /^(bg-|shadow(-|$)|rounded(-|$)|border(-|$)|z-)/;
const ACCENT_TINT = /accent-(wash|border)/;

describe('notificationActorCard', () => {
  it('renders the see-all control as a muted text control with a 44px hit area', () => {
    const seeAll = tokens(notificationActorCard().seeAll());

    expect(seeAll).toEqual(
      expect.arrayContaining([
        'text-xs',
        'font-medium',
        'text-foreground-muted',
        'tabular-nums',
        'hover:text-foreground',
        'outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
        'before:absolute',
        'before:inset-x-0',
        'before:-inset-y-3.5',
        'before:content-[""]',
      ]),
    );
    expect(seeAll).toContain('mt-0.5');
  });

  it('keeps the see-all control free of inert flex-child tokens', () => {
    const seeAll = tokens(notificationActorCard().seeAll());

    expect(seeAll).not.toContain('self-start');
    expect(seeAll).not.toContain('w-fit');
  });

  it('leaves the floating surface to the popover wrapper and only sets width and clipping', () => {
    const card = tokens(notificationActorCard().card());

    expect(card).toEqual(expect.arrayContaining(['w-64', 'overflow-hidden']));
    expect(card.filter((token) => SURFACE_TOKEN.test(token))).toEqual([]);
    expect(card).not.toContain('w-56');
  });

  it('lets the card run edge to edge so the dividers reach its border', () => {
    const card = tokens(notificationActorCard().card());

    expect(card.filter((token) => /^(p|px|py|pt|pb)-/.test(token))).toEqual([]);
    expect(card.filter((token) => token.startsWith('max-w-'))).toEqual([]);
  });

  it('pins the header as a quiet neutral band above the rows', () => {
    const theme = notificationActorCard();

    expect(tokens(theme.header())).toEqual(
      expect.arrayContaining([
        'flex',
        'items-center',
        'gap-2',
        'border-b',
        'border-border/70',
        'bg-muted/70',
        'px-3',
        'py-2',
      ]),
    );
    expect(tokens(theme.headerCount())).toEqual(
      expect.arrayContaining(['tabular-nums', 'text-foreground']),
    );
  });

  it('spends the card accent on the bookmark icon alone', () => {
    const theme = notificationActorCard();

    expect(tokens(theme.headerIcon())).toEqual(
      expect.arrayContaining(['size-4', 'shrink-0', 'text-accent']),
    );
    expect(tokens(theme.headerLabel())).toEqual(
      expect.arrayContaining([
        'flex-1',
        'truncate',
        'text-xs',
        'uppercase',
        'text-foreground-muted',
      ]),
    );
    for (const slot of [
      theme.headerLabel(),
      theme.headerCount(),
      theme.row(),
      theme.footer(),
    ]) {
      expect(tokens(slot)).not.toContain('text-accent');
    }
  });

  it('divides the actor rows neutrally and hovers on the shared surface', () => {
    const theme = notificationActorCard();

    expect(tokens(theme.row())).toEqual(
      expect.arrayContaining([
        'flex',
        'items-center',
        'gap-2.5',
        'px-3',
        'py-1',
        'border-b',
        'border-border/50',
        'last:border-b-0',
        'hover:bg-hover',
        'transition-colors',
        'duration-150',
      ]),
    );
    expect(tokens(notificationEntry().markRead())).toContain('hover:bg-hover');
    expect(tokens(theme.name())).toEqual(
      expect.arrayContaining([
        'min-w-0',
        'flex-1',
        'truncate',
        'text-sm',
        'font-medium',
        'text-foreground',
      ]),
    );
  });

  it('separates the visitor footer from the named rows instead of dimming one', () => {
    const theme = notificationActorCard();
    const footer = tokens(theme.footer());
    const row = tokens(theme.row());

    expect(footer).toEqual(
      expect.arrayContaining([
        'border-t',
        'border-border/70',
        'bg-muted/50',
        'text-xs',
        'text-foreground-muted',
      ]),
    );
    expect(footer).not.toContain('border-b');
    expect(footer.filter((token) => token.startsWith('hover:'))).toEqual([]);
    expect(row).not.toContain('text-foreground-muted');
    expect(row).not.toContain('border-t');
    expect(tokens(theme.footerIcon())).toEqual(
      expect.arrayContaining([
        'size-3.5',
        'shrink-0',
        'text-foreground-subtle',
      ]),
    );
  });

  it('gives the scrollable actor list a focus ring and no surface of its own', () => {
    const list = tokens(notificationActorCard().list());

    expect(list).toEqual(
      expect.arrayContaining([
        'flex',
        'flex-col',
        'outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
      ]),
    );
    expect(list.filter((token) => token.startsWith('max-h-'))).toEqual([]);
  });

  it('keeps the accent tint out of every card surface', () => {
    const theme = notificationActorCard();

    for (const slot of [
      theme.card(),
      theme.header(),
      theme.row(),
      theme.footer(),
    ]) {
      expect(tokens(slot).filter((token) => ACCENT_TINT.test(token))).toEqual(
        [],
      );
    }
  });
});
