import { cva } from 'class-variance-authority';

export const ThemeSwitchTheme = cva(
  'group relative inline-flex items-center justify-center cursor-pointer rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
  {
    variants: {
      variant: {
        default:
          'bg-white/80 dark:bg-gray-900/80 border border-gray-200/60 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-200/40 dark:hover:shadow-gray-900/40 focus-visible:ring-blue-500/20',
        minimal:
          'bg-transparent hover:bg-gray-100/60 dark:hover:bg-gray-800/60 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus-visible:ring-gray-400/20',
        glass:
          'bg-white/10 dark:bg-black/10 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-white/20 dark:hover:bg-black/20 hover:border-white/30 dark:hover:border-white/20 focus-visible:ring-white/30',
        floating:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 shadow-sm hover:shadow-xl hover:shadow-gray-200/30 dark:hover:shadow-gray-900/30 hover:-translate-y-0.5 focus-visible:ring-blue-500/20',
        pill: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:ring-gray-400/20',
      },
      size: {
        sm: 'h-8 w-8',
        md: 'h-9 w-9',
        lg: 'h-10 w-10',
        xl: 'h-12 w-12',
      },
      animation: {
        none: '',
        subtle: 'hover:scale-105 active:scale-95',
        bounce: 'hover:rotate-12',
        smooth: 'hover:scale-[1.02] active:scale-[0.98]',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      animation: 'subtle',
    },
  },
);

export const ThemeSwitchIcon = cva('transition-all duration-300 ease-out', {
  variants: {
    variant: {
      default: 'h-4 w-4',
      minimal: 'h-4 w-4',
      glass: 'h-4 w-4',
      floating: 'h-4 w-4',
      pill: 'h-4 w-4',
    },
    size: {
      sm: 'h-3 w-3',
      md: 'h-4 w-4',
      lg: 'h-5 w-5',
      xl: 'h-6 w-6',
    },
    animation: {
      none: '',
      subtle: 'group-hover:scale-110',
      bounce: 'group-hover:scale-110 group-active:scale-90',
      smooth: 'group-hover:scale-[1.02]',
      rotate: 'group-hover:rotate-180',
      scale: 'group-hover:scale-110',
      pulse: 'group-hover:animate-pulse',
      spin: 'group-hover:animate-spin-slow',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
    animation: 'rotate',
  },
});

export const ThemeSwitchContainer = cva('relative inline-flex items-center', {
  variants: {
    tooltip: {
      true: 'group/tooltip',
      false: '',
    },
  },
  defaultVariants: {
    tooltip: false,
  },
});

export const ThemeSwitchTooltip = cva(
  'absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-900 rounded-md opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg',
);
