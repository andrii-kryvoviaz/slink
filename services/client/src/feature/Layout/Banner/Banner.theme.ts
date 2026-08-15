import { cva } from 'class-variance-authority';

export const BannerTheme = cva(
  'flex flex-col gap-4 p-4 rounded-xl border shadow-sm transition-all duration-200',
  {
    variants: {
      variant: {
        default:
          'bg-gradient-to-r from-muted-subtle to-muted dark:from-muted dark:to-muted-subtle border-border/50',
        neutral:
          'bg-gradient-to-r from-card to-muted-subtle dark:to-muted border-border/50 shadow-muted-foreground/5',
        warning:
          'bg-gradient-to-r from-warning-subtle to-warning-subtle-strong border-warning-border/50 dark:border-warning-border/30 shadow-warning/10',
        info: 'bg-gradient-to-r from-info-subtle to-info-subtle-strong border-info-border/50 dark:border-info-border/30 shadow-info/10',
        success:
          'bg-gradient-to-r from-success-subtle to-success-subtle-strong border-success-border/50 dark:border-success-border/30 shadow-success/10',
        error:
          'bg-gradient-to-r from-danger-subtle to-danger-subtle-strong border-danger-border/50 dark:border-danger-border/30 shadow-danger/10',
        purple:
          'bg-gradient-to-r from-decor-violet/10 to-decor-violet/10 border-decor-violet/30 dark:border-decor-violet/20 shadow-decor-violet/10',
        violet:
          'bg-gradient-to-r from-decor-violet/10 to-decor-violet/10 border-decor-violet/30 dark:border-decor-violet/20 shadow-decor-violet/10',
      },
    },
  },
);

export const BannerIconTheme = cva(
  'flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] rounded-lg shadow-sm flex-shrink-0',
  {
    variants: {
      variant: {
        default: 'bg-foreground',
        neutral: 'bg-muted-foreground',
        warning:
          'bg-gradient-to-br from-warning to-warning-strong shadow-warning/30',
        info: 'bg-gradient-to-br from-info-fill to-info-strong shadow-info/30',
        success:
          'bg-gradient-to-br from-success to-success-strong shadow-success/30',
        error:
          'bg-gradient-to-br from-danger to-danger-strong shadow-danger/30',
        purple:
          'bg-gradient-to-br from-decor-violet to-decor-violet shadow-decor-violet/30',
        violet:
          'bg-gradient-to-br from-decor-violet to-decor-violet shadow-decor-violet/30',
      },
    },
  },
);

export const BannerIconColorTheme = cva('h-5 w-5', {
  variants: {
    variant: {
      default: 'text-background',
      neutral: 'text-background',
      warning: 'text-white',
      info: 'text-white',
      success: 'text-white',
      error: 'text-white',
      purple: 'text-decor-violet-foreground',
      violet: 'text-decor-violet-foreground',
    },
  },
});

export const BannerActionTheme = cva('border transition-colors duration-200', {
  variants: {
    variant: {
      default:
        'bg-accent-subtle/80 hover:bg-accent-subtle border-accent-border hover:border-accent text-accent-subtle-foreground',
      neutral:
        'bg-muted/80 hover:bg-ghost-hover-strong border-border hover:border-border-strong dark:hover:border-border-stronger text-foreground-soft',
      warning:
        'bg-warning-subtle/90 hover:bg-warning-subtle dark:bg-warning-strong/25 dark:hover:bg-warning-strong/40 border-warning-border hover:border-warning text-warning-subtle-foreground',
      info: 'bg-info-subtle/80 hover:bg-info-subtle dark:bg-info-fill/30 dark:hover:bg-info-fill/45 border-info-border hover:border-info text-info-subtle-foreground',
      success:
        'bg-success-subtle/80 hover:bg-success-subtle dark:bg-success-fill/30 dark:hover:bg-success-fill/45 border-success-border hover:border-success text-success-subtle-foreground',
      error:
        'bg-danger-subtle/80 hover:bg-danger-subtle dark:bg-danger-fill/30 dark:hover:bg-danger-fill/45 border-danger-border hover:border-danger text-danger-subtle-foreground',
      purple:
        'bg-decor-violet/10 hover:bg-decor-violet/20 dark:bg-decor-violet/30 dark:hover:bg-decor-violet/45 border-decor-violet/40 hover:border-decor-violet text-decor-violet',
      violet:
        'bg-decor-violet/10 hover:bg-decor-violet/20 dark:bg-decor-violet/30 dark:hover:bg-decor-violet/45 border-decor-violet/40 hover:border-decor-violet text-decor-violet',
    },
  },
});

export const BannerFooterTheme = cva('pt-3 border-t', {
  variants: {
    variant: {
      default: 'border-border/20',
      neutral: 'border-border/20',
      warning: 'border-warning-border/20',
      info: 'border-info-border/20',
      success: 'border-success-border/20',
      error: 'border-danger-border/20',
      purple: 'border-decor-violet/20',
      violet: 'border-decor-violet/20',
    },
  },
});

export const BannerFooterTextTheme = cva(
  'flex items-center justify-center gap-2 text-xs font-medium',
  {
    variants: {
      variant: {
        default: 'text-muted-foreground/80',
        neutral: 'text-muted-foreground/80',
        warning: 'text-warning-subtle-foreground/80',
        info: 'text-info-subtle-foreground/80',
        success: 'text-success-subtle-foreground/80',
        error: 'text-danger-subtle-foreground/80',
        purple: 'text-decor-violet/80',
        violet: 'text-decor-violet/80',
      },
    },
  },
);
