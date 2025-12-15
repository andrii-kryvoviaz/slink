import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const hoverCardVariants = cva(
  'z-50 outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
  {
    variants: {
      variant: {
        default:
          'bg-popover text-popover-foreground border border-border shadow-md',
        dark: 'bg-neutral-900 text-white border border-white/10 shadow-xl',
        glass:
          'bg-white/80 text-slate-900 border border-slate-200/30 shadow-lg backdrop-blur-md backdrop-saturate-150 dark:bg-slate-900/80 dark:text-slate-100 dark:border-slate-700/30',
      },
      size: {
        sm: 'p-3',
        md: 'p-4',
        lg: 'p-5',
      },
      rounded: {
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
      },
      width: {
        auto: 'w-auto',
        sm: 'w-64',
        md: 'w-72',
        lg: 'w-80',
        xl: 'w-96',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'md',
      width: 'sm',
    },
  },
);

export type HoverCardVariantProps = VariantProps<typeof hoverCardVariants>;
export type HoverCardVariant = NonNullable<HoverCardVariantProps['variant']>;
export type HoverCardSize = NonNullable<HoverCardVariantProps['size']>;
export type HoverCardRounded = NonNullable<HoverCardVariantProps['rounded']>;
export type HoverCardWidth = NonNullable<HoverCardVariantProps['width']>;
