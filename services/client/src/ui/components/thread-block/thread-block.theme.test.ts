import { describe, expect, it } from 'vitest';

import { threadBlock } from './thread-block.theme';

const tokens = (classes: string) =>
  classes.split(/\s+/).filter(Boolean).toSorted();

describe('threadBlock', () => {
  const theme = threadBlock();

  it('keeps the borderless soft surface on the root', () => {
    expect(tokens(theme.root())).toEqual(
      tokens('flex flex-col gap-2.5 rounded-lg bg-muted-soft px-3 py-2'),
    );
  });

  it('drops the surface on the bare root', () => {
    expect(tokens(threadBlock({ surface: 'bare' }).root())).toEqual(
      tokens('flex flex-col gap-2.5'),
    );
  });

  it('keeps the hit area and focus ring on the trigger', () => {
    expect(tokens(theme.trigger())).toEqual(
      tokens(
        'group relative inline-flex w-fit items-center gap-1 self-start rounded-sm text-xs font-medium text-foreground-muted outline-none hover:text-foreground before:absolute before:inset-x-0 before:-inset-y-3.5 before:content-[""] focus-visible:ring-2 focus-visible:ring-ring/50',
      ),
    );
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
        'flex flex-col gap-2.5 overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none',
      ),
    );
  });
});
