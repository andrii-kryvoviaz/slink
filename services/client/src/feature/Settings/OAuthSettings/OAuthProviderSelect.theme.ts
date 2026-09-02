import { cva } from 'class-variance-authority';

export const providerSelectTileTheme = cva(
  'group relative flex flex-col items-center gap-3 rounded-2xl border bg-card dark:bg-card/50 p-6 transition-all duration-200 cursor-pointer',
  {
    variants: {
      intent: {
        provider:
          'border-border/60 hover:border-border-strong dark:hover:border-border hover:shadow-lg hover:shadow-border/50 dark:hover:shadow-card/50 hover:-translate-y-0.5',
        custom:
          'justify-center border-dashed border-border-strong/60 dark:border-border/40 hover:border-foreground-subtle dark:hover:border-border hover:shadow-lg hover:shadow-border/50 dark:hover:shadow-card/50 hover:-translate-y-0.5',
      },
    },
    defaultVariants: {
      intent: 'provider',
    },
  },
);

export const providerSelectIconTheme = cva(
  'flex items-center justify-center w-12 h-12 rounded-xl bg-muted transition-transform duration-300 group-hover:scale-110',
);
