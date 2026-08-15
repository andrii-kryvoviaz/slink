import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const UploaderContainerTheme = cva(
  'relative group rounded-xl border-2 transition-all duration-200',
  {
    variants: {
      state: {
        dragOver: 'border-dashed border-accent/50 scale-[1.01]',
        disabled: 'border-transparent opacity-60',
        default: 'border-transparent',
      },
    },
    defaultVariants: {
      state: 'default',
    },
  },
);

export type UploaderContainerState = NonNullable<
  Parameters<typeof UploaderContainerTheme>[0]
>['state'];

export const UploaderSurfaceTheme = cva(
  'relative bg-card transition-colors duration-500 has-[[data-slot=dropzone-input]:hover]:bg-muted',
);

export const UploaderHeroTheme = cva(
  'relative w-full cursor-pointer transition-all duration-500',
  {
    variants: {
      disabled: {
        true: 'pointer-events-none opacity-60',
        false: '',
      },
    },
    defaultVariants: {
      disabled: false,
    },
  },
);

export const UploaderDragOverlayTheme = cva(
  'absolute inset-0 bg-gradient-to-br z-20 from-accent/20 to-accent-strong/20 rounded-xl transition-opacity duration-200 flex items-center justify-center backdrop-blur-md pointer-events-none',
);

export const UploaderConstraintsTheme = tv({
  slots: {
    base: 'flex w-full flex-col items-start gap-x-6 border-t border-border/60 pt-3.5 text-left dark:border-border/30 sm:flex-row sm:justify-between',
    column: 'flex w-full min-w-0 flex-col gap-y-[7px] sm:w-auto',
    labelRow: 'flex w-full items-baseline justify-between gap-x-3',
    label:
      'text-[10px] font-semibold uppercase tracking-[0.09em] text-foreground-muted dark:text-foreground-subtle',
    formats: 'text-[13px] font-medium leading-relaxed text-foreground-muted',
    toggle:
      'ml-[0.45em] cursor-pointer font-medium text-foreground-muted underline decoration-dotted decoration-ring/70 underline-offset-[3px] transition-colors hover:text-accent hover:decoration-accent/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/50 dark:text-foreground-subtle',
    inlineSize:
      'whitespace-nowrap text-[13px] font-medium text-foreground-muted sm:hidden',
    sizeValue:
      'text-[13px] font-semibold text-foreground dark:text-foreground-soft',
    maxSizeColumn:
      'hidden flex-shrink-0 flex-col items-end gap-y-[7px] text-right sm:flex',
  },
});
