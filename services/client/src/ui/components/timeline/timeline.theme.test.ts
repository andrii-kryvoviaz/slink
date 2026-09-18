import { describe, expect, it } from 'vitest';

import { timeline } from './timeline.theme';

const tokens = (classes: string) =>
  classes.split(/\s+/).filter(Boolean).toSorted();

const dotBase = 'size-[7px] rounded-full border';
const lineTokenPattern = /^(border-[btlrxy](-|$)|divide-)/;
const haloTokenPattern = /^(ring|shadow)/;

describe('timeline', () => {
  it.each([
    [{ active: true }, `${dotBase} bg-accent border-accent`],
    [{ active: false }, `${dotBase} bg-background border-border-strong`],
  ])('renders the dot for %o with a stable class set', (props, expected) => {
    expect(tokens(timeline(props).dot())).toEqual(tokens(expected));
  });

  it('defaults the dot to the hollow variant', () => {
    expect(tokens(timeline().dot())).toEqual(
      tokens(timeline({ active: false }).dot()),
    );
  });

  it('keeps the root classes', () => {
    expect(tokens(timeline().root())).toEqual(
      tokens('relative flex flex-col gap-7'),
    );
  });

  it('keeps the rail classes', () => {
    expect(tokens(timeline().rail())).toEqual(
      tokens('absolute inset-y-0 left-[3px] w-px bg-border'),
    );
  });

  it('keeps the label classes', () => {
    expect(tokens(timeline().label())).toEqual(
      tokens('text-xs text-foreground-muted pl-[19px] pb-2'),
    );
  });

  it('keeps the entry grid classes', () => {
    expect(tokens(timeline().entry())).toEqual(
      tokens(
        'relative grid grid-cols-[7px_minmax(0,1fr)] gap-x-3 py-2 items-start',
      ),
    );
  });

  it.each([true, false])(
    'draws no line other than the rail when active is %s',
    (active) => {
      const theme = timeline({ active });
      const slots = [
        theme.root(),
        theme.rail(),
        theme.group(),
        theme.label(),
        theme.entry(),
        theme.dotCell(),
        theme.dot(),
        theme.content(),
      ];
      const all = slots.flatMap(tokens);

      expect(all.filter((token) => lineTokenPattern.test(token))).toEqual([]);
      expect(all.filter((token) => haloTokenPattern.test(token))).toEqual([]);
      expect(all.filter((token) => token === 'bg-border')).toEqual([
        'bg-border',
      ]);
      expect(tokens(theme.rail())).toContain('bg-border');
    },
  );
});
