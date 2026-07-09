import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const UploaderContainerTheme = cva(
  'relative group rounded-xl border-2 transition-all duration-200',
  {
    variants: {
      state: {
        dragOver:
          'border-dashed border-indigo-400 dark:border-indigo-400 scale-[1.01]',
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
  'relative bg-card-primary transition-colors duration-500 has-[[data-slot=dropzone-input]:hover]:bg-card-secondary',
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
  'absolute inset-0 bg-gradient-to-br z-20 from-indigo-500/20 to-purple-500/20 rounded-xl transition-opacity duration-200 flex items-center justify-center backdrop-blur-md pointer-events-none',
);

export const UploaderConstraintsTheme = tv({
  slots: {
    base: 'flex w-full flex-col items-start gap-x-6 border-t border-slate-200/60 pt-[22px] text-left dark:border-white/[0.06] sm:flex-row sm:justify-between',
    column: 'flex w-full min-w-0 flex-col gap-y-[7px] sm:w-auto',
    labelRow: 'flex w-full items-baseline justify-between gap-x-3',
    label: 'text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500',
    formats:
      'text-[15px] font-medium leading-relaxed text-slate-600 dark:text-slate-400',
    formatText: '[word-spacing:0.2em]',
    toggle:
      'ml-[0.45em] cursor-pointer font-medium text-slate-500 underline decoration-dotted decoration-slate-400/70 underline-offset-[3px] transition-colors hover:text-violet-600 hover:decoration-violet-400/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400/50 dark:text-slate-500 dark:decoration-slate-500/70 dark:hover:text-violet-400 dark:hover:decoration-violet-400/60',
    inlineSize:
      'whitespace-nowrap text-[15px] font-medium text-slate-600 dark:text-slate-400 sm:hidden',
    sizeValue: 'text-[15px] font-semibold text-slate-900 dark:text-slate-100',
    maxSizeColumn:
      'hidden flex-shrink-0 flex-col items-end gap-y-[7px] text-right sm:flex',
  },
});
