import { describe, expect, it, vi } from 'vitest';

import { exploreListRowTheme } from './Explore/ExploreView.theme';
import { imageListRowVariants } from './ImageView.theme';

vi.mock('@slink/utils/i18n', () => ({}));

const tokens = (classes: string) =>
  classes.split(/\s+/).filter(Boolean).toSorted();

const rowBase =
  'group relative flex flex-col @xl:flex-row w-full overflow-hidden rounded-lg border bg-card dark:bg-card/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-surface-inverse/50';
const subtleBorder =
  'border-foreground-subtle/25 hover:border-foreground-subtle/50';
const selectedRing =
  'bg-primary-solid/8 border-info-border dark:border-primary-solid ring-2 ring-primary-solid';

describe('imageListRowVariants', () => {
  it.each([
    [{}, `${rowBase} ${subtleBorder}`],
    [{ selected: true }, `${rowBase} ${selectedRing}`],
    [{ selectionMode: true }, `${rowBase} cursor-pointer ${subtleBorder}`],
    [
      { selected: true, selectionMode: true },
      `${rowBase} ${selectedRing} cursor-pointer`,
    ],
  ])('renders %o with a stable class set', (props, expected) => {
    expect(tokens(imageListRowVariants(props))).toEqual(tokens(expected));
  });
});

describe('exploreListRowTheme', () => {
  const theme = exploreListRowTheme();

  it('keeps the row root classes', () => {
    expect(tokens(theme.root())).toEqual(
      tokens(`${rowBase} ${subtleBorder} cursor-pointer @xl:min-h-28`),
    );
  });

  it('keeps the list view rail classes', () => {
    expect(tokens(theme.listRail())).toEqual(
      tokens(
        'relative w-full @xl:w-40 @2xl:w-44 shrink-0 bg-muted dark:bg-muted/80 block overflow-hidden',
      ),
    );
  });
});
