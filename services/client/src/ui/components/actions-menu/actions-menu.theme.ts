import { type VariantProps, tv } from 'tailwind-variants';

export const actionsMenuTriggerTheme = tv({
  base: 'inline-flex items-center justify-center rounded-md transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-500/30',
  variants: {
    tone: {
      ghost:
        'p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50',
      dark: 'p-1 text-white/40 hover:text-white/70 hover:bg-white/5 focus-visible:ring-white/30 focus-visible:ring-offset-transparent',
    },
  },
  defaultVariants: {
    tone: 'ghost',
  },
});

type ThemeTone = VariantProps<typeof actionsMenuTriggerTheme>['tone'];

export type ActionsMenuTone = ThemeTone | 'surface';
