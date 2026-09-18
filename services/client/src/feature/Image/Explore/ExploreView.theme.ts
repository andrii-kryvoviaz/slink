import { tv } from 'tailwind-variants';

import { cn } from '@slink/utils/ui';

import { imageCardVariants, imageListRowVariants } from '../ImageView.theme';

export const exploreCardTheme = tv({
  slots: {
    root: cn(imageCardVariants({ border: 'token' }), 'cursor-pointer'),
    imageWrapper: 'relative',
    overlay:
      'absolute inset-0 bg-linear-to-t from-scrim/60 via-scrim/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200',
    expandOverlay:
      'absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none',
    expandCircle:
      'w-14 h-14 rounded-full bg-scrim/50 flex items-center justify-center',
    badges: 'absolute bottom-2 left-2 flex items-center gap-1.5',
    licenseWrapper: 'absolute bottom-2 right-2',
    body: 'p-3',
    avatarRow: 'flex items-center gap-2.5',
    nameWrapper: 'flex-1 min-w-0',
    name: 'font-medium text-foreground text-sm leading-tight truncate',
    date: 'text-xs text-foreground-muted mt-0.5',
    description: 'mt-3 text-sm text-foreground-muted leading-relaxed',
  },
});

export const exploreListRowTheme = tv({
  slots: {
    list: '@container flex flex-col gap-3',
    root: cn(imageListRowVariants(), 'cursor-pointer @xl:min-h-28'),
    rail: 'relative w-full @xl:w-40 @2xl:w-44 shrink-0 bg-muted dark:bg-muted/80',
    listRail:
      'relative w-full @xl:w-40 @2xl:w-44 shrink-0 bg-muted dark:bg-muted/80 block overflow-hidden',
    frame: 'aspect-4/3 w-full @xl:absolute @xl:inset-0 @xl:aspect-auto',
    badges: 'absolute bottom-2 left-2 flex items-center gap-1.5',
    body: 'flex flex-col flex-1 gap-1.5 p-3 @xl:px-4 @xl:py-3 min-w-0',
    header: 'flex items-center justify-between gap-3',
    avatarWrapper: 'flex items-center gap-2.5 min-w-0 flex-1',
    name: 'font-medium text-foreground text-sm leading-tight truncate',
    bookmark:
      'flex items-center gap-1 text-xs text-foreground-muted shrink-0 tabular-nums',
    description: 'text-sm text-foreground-muted truncate',
    noDescription: 'text-sm text-foreground-subtle truncate',
    tags: 'flex flex-wrap items-center gap-2',
    skeletonRow:
      'flex flex-col @xl:flex-row w-full overflow-hidden rounded-lg border border-border bg-card dark:bg-card/60 @xl:min-h-28',
    skeletonBadge: 'absolute bottom-2 left-2',
    skeletonAvatar: 'flex items-center gap-2.5',
    skeletonChips: 'flex gap-2',
  },
});
