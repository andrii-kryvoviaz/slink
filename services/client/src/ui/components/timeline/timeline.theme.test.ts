import { describe, expect, it } from 'vitest';

import { timeline } from './timeline.theme';

const tokens = (classes: string) =>
  classes.split(/\s+/).filter(Boolean).toSorted();

const railTokenPattern =
  /^(bg-border|border|rounded-full|absolute)|^(left|pl)-\[/;

describe('timeline', () => {
  it('keeps the root classes', () => {
    expect(tokens(timeline().root())).toEqual(tokens('flex flex-col gap-7'));
  });

  it('keeps the group classes', () => {
    expect(tokens(timeline().group())).toEqual(tokens('flex flex-col'));
  });

  it('keeps the label classes', () => {
    expect(tokens(timeline().label())).toEqual(
      tokens('pb-2 text-xs text-foreground-muted'),
    );
  });

  it('draws no rail and no dots', () => {
    const theme = timeline();
    const all = [theme.root(), theme.group(), theme.label()].flatMap(tokens);

    expect(all.filter((token) => railTokenPattern.test(token))).toEqual([]);
    expect('rail' in theme).toBe(false);
    expect('dot' in theme).toBe(false);
    expect('entry' in theme).toBe(false);
  });
});
