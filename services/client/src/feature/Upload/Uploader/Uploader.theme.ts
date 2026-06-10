import { cva } from 'class-variance-authority';

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

export const UploaderPatternTheme = cva(
  'absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(148,163,184,0.12)_1px,transparent_0)] bg-[length:20px_20px] dark:bg-[radial-gradient(circle_at_1px_1px,rgba(71,85,105,0.25)_1px,transparent_0)]',
);

export const UploaderDragOverlayTheme = cva(
  'absolute inset-0 bg-gradient-to-br z-10 from-indigo-500/20 to-purple-500/20 rounded-xl transition-opacity duration-200 flex items-center justify-center backdrop-blur-md pointer-events-none',
);
