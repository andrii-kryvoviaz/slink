import { describe, expect, it } from 'vitest';

import { threadBlock } from './thread-block.theme';

const tokens = (classes: string) =>
  classes.split(/\s+/).filter(Boolean).toSorted();

describe('threadBlock', () => {
  const theme = threadBlock();

  it('draws the quote rail on the root around a latest row', () => {
    expect(tokens(theme.root())).toEqual(
      tokens('flex flex-col gap-2.5 border-l-2 border-border pl-3 pr-3'),
    );
  });

  it('renders rows in muted text', () => {
    expect(tokens(theme.row())).toEqual(
      tokens('min-w-0 text-[13px] text-foreground-muted'),
    );
  });

  it('keeps the panel bare inside the rail root', () => {
    expect(tokens(theme.panel())).toEqual(tokens('flex flex-col gap-2.5'));
  });

  it('moves the rail from the root to the panel without a latest row', () => {
    const list = threadBlock({ latest: false });

    expect(tokens(list.root())).toEqual(tokens('flex flex-col gap-2.5'));
    expect(tokens(list.panel())).toEqual(
      tokens('flex flex-col gap-2.5 border-l-2 border-border pl-3 pr-3'),
    );
  });

  it.each([
    { latest: true, surface: threadBlock({ latest: true }).root() },
    { latest: false, surface: threadBlock({ latest: false }).panel() },
  ])(
    'renders the rail as a bare hairline when latest is $latest',
    ({ surface }) => {
      const rail = tokens(surface);

      expect(rail).toEqual(
        expect.arrayContaining(['border-l-2', 'border-border', 'pl-3']),
      );
      expect(
        rail.filter((token) => /(^|:)(bg-|rounded-|py-)/.test(token)),
      ).toEqual([]);
    },
  );

  it('keeps the hit area and focus ring on the trigger', () => {
    expect(tokens(theme.trigger())).toEqual(
      tokens(
        'group relative inline-flex w-fit items-center gap-1 self-start rounded-sm text-xs font-medium text-foreground-muted outline-none hover:text-foreground before:absolute before:inset-x-0 before:-inset-y-3.5 before:content-[""] focus-visible:ring-2 focus-visible:ring-ring/50',
      ),
    );
  });

  it('styles the show-more control exactly like the trigger', () => {
    expect(tokens(theme.more())).toEqual(tokens(theme.trigger()));
  });

  it('sizes the show-more chevron like the trigger chevron but never rotates it', () => {
    const chevron = tokens(theme.moreChevron());

    expect(chevron).toContain('size-3.5');
    expect(tokens(theme.chevron())).toContain('size-3.5');
    expect(chevron.filter((token) => token.includes('rotate'))).toEqual([]);
  });

  it('renders the trigger as quiet muted text', () => {
    const trigger = tokens(theme.trigger());

    expect(trigger).toEqual(
      expect.arrayContaining([
        'text-foreground-muted',
        'hover:text-foreground',
      ]),
    );
    expect(trigger).not.toContain('text-accent-text');
    expect(
      trigger.filter((token) =>
        /(^|:)(underline$|border(-|$)|bg-)/.test(token),
      ),
    ).toEqual([]);
  });

  it('keeps the collapsible animations with reduced motion on the content', () => {
    expect(tokens(theme.content())).toEqual(
      tokens(
        'overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none',
      ),
    );
  });
});
