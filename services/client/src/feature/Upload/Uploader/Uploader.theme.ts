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
    base: 'relative z-10 flex flex-wrap items-center justify-center gap-x-2.5 gap-y-1 px-4 pt-3 pb-4 text-[11px] text-slate-400 sm:pt-2 sm:pb-5 sm:text-xs dark:text-slate-500',
    formats:
      'flex flex-wrap items-center justify-center gap-x-1.5 gap-y-0.5 text-slate-500 dark:text-slate-400',
    separator: 'h-3 w-px shrink-0 bg-slate-300/70 dark:bg-slate-600/60',
    toggle:
      'cursor-pointer rounded font-medium text-blue-600 transition-colors hover:text-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/50 dark:text-blue-300 dark:hover:text-blue-200',
    maxSize: 'shrink-0 whitespace-nowrap',
  },
});
