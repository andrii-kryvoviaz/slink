import { describe, expect, it } from 'vitest';

import { notificationEntry } from './NotificationEntry.theme';

const tokens = (classes: string) => classes.split(/\s+/).filter(Boolean);

const hideTokens = [
  'group-hover/entry:opacity-0',
  'group-focus-within/entry:opacity-0',
  '[@media(hover:none)]:opacity-0',
];

describe('notificationEntry', () => {
  it('keeps the time visible on read entries', () => {
    const time = tokens(notificationEntry({ read: true }).time());

    expect(time.filter((token) => token.endsWith('opacity-0'))).toEqual([]);
  });

  it('hides the time behind the mark-read control on unread entries', () => {
    const time = tokens(notificationEntry({ read: false }).time());

    expect(time).toEqual(expect.arrayContaining(hideTokens));
  });

  it('renders the type badge as a hairline circle past the thumbnail corner', () => {
    const badge = tokens(notificationEntry().badge());

    expect(badge).toEqual(
      expect.arrayContaining([
        'absolute',
        'border',
        'size-[18px]',
        'rounded-full',
        'bg-background',
        'border-border-strong',
        '-right-[5px]',
        '-bottom-[5px]',
      ]),
    );
  });

  it('keeps the badge icon monochrome', () => {
    const icon = tokens(notificationEntry().badgeIcon());

    expect(icon).toEqual(
      expect.arrayContaining(['text-foreground-muted', 'size-[11px]']),
    );
    expect(
      icon.filter((token) =>
        /text-(accent|primary|success|warning|danger|info)/.test(token),
      ),
    ).toEqual([]);
  });

  it('gives the thumbnail button a focus ring and a 44px hit area', () => {
    const thumbButton = tokens(notificationEntry().thumbButton());

    expect(thumbButton).toEqual(
      expect.arrayContaining([
        'focus-visible:ring-2',
        'focus-visible:ring-ring/50',
        'outline-none',
        'before:absolute',
        'before:-inset-0.5',
        'before:content-[""]',
      ]),
    );
  });

  it('keeps the row inert', () => {
    const row = tokens(notificationEntry().row());

    expect(row).not.toContain('cursor-pointer');
    expect(row).not.toContain('focus-visible:ring-2');
  });

  it('anchors the badge on a relative thumbnail wrapper', () => {
    const { thumbButton, thumb, image } = notificationEntry();

    expect(tokens(thumbButton())).toEqual(
      expect.arrayContaining(['relative', 'size-10']),
    );
    expect(tokens(thumb())).toEqual(
      expect.arrayContaining(['overflow-hidden', 'rounded-lg', 'size-10']),
    );
    expect(tokens(image())).toEqual(
      expect.arrayContaining(['h-10', 'w-10', 'object-cover']),
    );
  });

  it('dims the thumbnail and badge together on read entries', () => {
    const { thumbButton, thumb } = notificationEntry({ read: true });

    expect(tokens(thumbButton())).toContain('opacity-60');
    expect(
      tokens(thumb()).filter((token) => /(^|:)opacity-/.test(token)),
    ).toEqual([]);
  });

  it('does not dim unread entries', () => {
    const theme = notificationEntry({ read: false });
    const slots = [
      theme.thumbButton(),
      theme.thumb(),
      theme.badge(),
      theme.badgeIcon(),
      theme.image(),
    ];

    expect(slots.flatMap(tokens)).not.toContain('opacity-60');
  });
});
