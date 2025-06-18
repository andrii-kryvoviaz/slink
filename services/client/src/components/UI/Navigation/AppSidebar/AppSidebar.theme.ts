import { cva } from 'class-variance-authority';

export const AppSidebarTheme = cva(
  'flex h-full flex-col bg-card/20 border-0 transition-all duration-300 ease-out md:relative',
  {
    variants: {
      variant: {
        default: '',
        floating: 'shadow-xl',
        minimal: 'shadow-none bg-transparent',
      },
      size: {
        sm: 'w-16',
        md: 'w-64',
        lg: 'w-80',
        collapsed: 'w-16',
        expanded: 'w-64',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const AppSidebarHeader = cva(
  'flex items-center px-4 py-3 mb-1 relative z-40 bg-transparent',
  {
    variants: {
      collapsed: {
        true: 'justify-center px-4',
        false: 'justify-between px-4',
      },
    },
  },
);

export const AppSidebarContent = cva(
  'flex-1 overflow-y-auto px-3 pb-3 space-y-6 scrollbar-none',
);

export const AppSidebarFooter = cva('px-3 py-2 mt-auto');

export const AppSidebarGroup = cva('space-y-1');

export const AppSidebarGroupTitle = cva(
  'mb-2 px-3 text-xs font-medium text-muted-foreground/70 tracking-wide select-none uppercase',
  {
    variants: {
      collapsed: {
        true: 'sr-only',
        false: 'block',
      },
    },
  },
);

export const AppSidebarItem = cva(
  'group relative flex w-full items-center rounded-lg text-sm font-medium transition-all duration-200 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/20 focus-visible:ring-offset-1 disabled:pointer-events-none disabled:opacity-50 select-none hover:scale-[1.02] active:scale-[0.98] cursor-pointer',
  {
    variants: {
      variant: {
        default:
          'text-muted-foreground hover:text-foreground hover:bg-muted/70 hover:shadow-sm hover:shadow-black/5 dark:hover:bg-muted/50 dark:hover:shadow-black/10',
        active:
          'text-indigo-700 dark:text-indigo-300 bg-indigo-50/80 dark:bg-indigo-950/30 shadow-sm hover:bg-indigo-100/90 dark:hover:bg-indigo-950/40 hover:shadow-indigo-500/10',
        destructive:
          'text-red-600 hover:text-red-700 hover:bg-red-50/70 hover:shadow-sm hover:shadow-red-500/10 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950/30',
      },
      collapsed: {
        true: 'justify-center px-2 py-2 w-10 h-10 gap-0',
        false: 'justify-start px-3 py-2 gap-3',
      },
    },
    compoundVariants: [
      {
        variant: 'active',
        collapsed: false,
        class: 'border-l-2 border-indigo-500 dark:border-indigo-400 pl-2',
      },
    ],
    defaultVariants: {
      variant: 'default',
      collapsed: false,
    },
  },
);

export const AppSidebarIcon = cva(
  'h-4 w-4 shrink-0 transition-all duration-200 group-hover:scale-110',
  {
    variants: {
      variant: {
        default: '',
        active: 'text-indigo-600 dark:text-indigo-400',
        destructive: '',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const AppSidebarText = cva(
  'truncate transition-all duration-300 font-medium',
  {
    variants: {
      collapsed: {
        true: 'w-0 opacity-0 translate-x-2',
        false: 'opacity-100 translate-x-0',
      },
    },
  },
);

export const AppSidebarBadge = cva(
  'ml-auto h-5 px-2 inline-flex items-center justify-center rounded-full text-xs font-medium transition-all duration-300 group-hover:scale-105',
  {
    variants: {
      variant: {
        primary: 'bg-muted/60 text-foreground group-hover:bg-muted/80',
        secondary: 'bg-muted/40 text-muted-foreground group-hover:bg-muted/60',
        success:
          'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/30',
        warning:
          'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/30',
        danger:
          'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400 group-hover:bg-red-200 dark:group-hover:bg-red-900/30',
      },
      collapsed: {
        true: 'w-0 opacity-0 scale-0',
        false: 'opacity-100 scale-100',
      },
    },
    defaultVariants: {
      variant: 'primary',
      collapsed: false,
    },
  },
);

export const AppSidebarUserSection = cva(
  'group flex items-center gap-3 rounded-lg p-2.5 mx-1 transition-all duration-200 hover:bg-muted/50 dark:hover:bg-muted/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/20 border-0 hover:scale-[1.01] active:scale-[0.99] hover:shadow-sm',
  {
    variants: {
      collapsed: {
        true: 'justify-center p-2 w-10 h-10',
        false: 'justify-start p-2.5',
      },
    },
  },
);

export const AppSidebarUserAvatar = cva(
  'h-8 w-8 shrink-0 transition-all duration-200',
);

export const AppSidebarUserInfo = cva(
  'min-w-0 flex-1 transition-all duration-300',
  {
    variants: {
      collapsed: {
        true: 'w-0 opacity-0 translate-x-2',
        false: 'opacity-100 translate-x-0',
      },
    },
  },
);

export const AppSidebarCollapseButton = cva(
  'inline-flex h-8 w-8 items-center justify-center rounded-full text-foreground/70 hover:text-foreground bg-background/40 hover:bg-background/60 dark:bg-background/30 dark:hover:bg-background/50 backdrop-blur-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 transition-all duration-200 ease-out hover:scale-105 active:scale-95 border-0 shadow-sm shadow-black/5 dark:shadow-black/10',
);
