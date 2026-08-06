import { type VariantProps, tv } from 'tailwind-variants';

export const actionsMenuTriggerTheme = tv({
  base: 'inline-flex items-center justify-center rounded-md transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-ring/30',
  variants: {
    tone: {
      ghost:
        'p-1.5 text-foreground-subtle hover:text-foreground-soft hover:bg-muted',
      dark: 'p-1 text-surface-inverse-foreground/40 hover:text-surface-inverse-foreground/70 hover:bg-surface-inverse-foreground/5 focus-visible:ring-surface-inverse-foreground/30 focus-visible:ring-offset-transparent',
    },
  },
  defaultVariants: {
    tone: 'ghost',
  },
});

type ThemeTone = VariantProps<typeof actionsMenuTriggerTheme>['tone'];

export type ActionsMenuTone = ThemeTone | 'surface';
