import { cva } from 'class-variance-authority';

export const BannerTheme = cva(
  'flex flex-col gap-4 p-4 rounded-xl border shadow-sm transition-all duration-200',
  {
    variants: {
      variant: {
        default:
          'bg-gradient-to-r from-muted-soft to-muted dark:from-muted dark:to-muted-soft border-border/50',
        neutral:
          'bg-gradient-to-r from-card to-muted-soft dark:to-muted border-border/50 shadow-foreground-muted/5',
        warning:
          'bg-gradient-to-r from-warning-wash dark:from-warning-wash/20 to-warning-wash-strong dark:to-warning-wash-strong/20 border-warning-border/50 dark:border-warning-border/9 shadow-warning/10',
        info: 'bg-gradient-to-r from-info-wash dark:from-info-wash/20 to-info-wash-strong dark:to-info-wash-strong/20 border-info-border/50 dark:border-info-border/9 shadow-info/10',
        success:
          'bg-gradient-to-r from-success-wash dark:from-success-wash/20 to-success-wash-strong dark:to-success-wash-strong/20 border-success-border/50 dark:border-success-border/9 shadow-success/10',
        error:
          'bg-gradient-to-r from-danger-wash dark:from-danger-wash/20 to-danger-wash-strong dark:to-danger-wash-strong/20 border-danger-border/50 dark:border-danger-border/9 shadow-danger/10',
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
        neutral: 'bg-foreground-muted',
        warning:
          'bg-gradient-to-br from-warning to-warning-strong shadow-warning/30',
        info: 'bg-gradient-to-br from-info-solid to-info-strong shadow-info/30',
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
      purple: 'text-on-decor-violet',
      violet: 'text-on-decor-violet',
    },
  },
});

export const BannerActionTheme = cva('border transition-colors duration-200', {
  variants: {
    variant: {
      default:
        'bg-accent-wash/80 dark:bg-accent-wash/16 hover:bg-accent-wash dark:hover:bg-accent-wash/20 border-accent-border dark:border-accent-border/30 hover:border-accent text-accent-text',
      neutral:
        'bg-muted/80 hover:bg-hover-strong border-border hover:border-border-strong dark:hover:border-border-bold text-foreground-soft',
      warning:
        'bg-warning-wash/90 hover:bg-warning-wash dark:bg-warning-strong/25 dark:hover:bg-warning-strong/40 border-warning-border dark:border-warning-border/30 hover:border-warning text-warning-text',
      info: 'bg-info-wash/80 hover:bg-info-wash dark:bg-info-solid/30 dark:hover:bg-info-solid/45 border-info-border dark:border-info-border/30 hover:border-info text-info-text',
      success:
        'bg-success-wash/80 hover:bg-success-wash dark:bg-success-solid/30 dark:hover:bg-success-solid/45 border-success-border dark:border-success-border/30 hover:border-success text-success-text',
      error:
        'bg-danger-wash/80 hover:bg-danger-wash dark:bg-danger-solid/30 dark:hover:bg-danger-solid/45 border-danger-border dark:border-danger-border/30 hover:border-danger text-danger-text',
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
      warning: 'border-warning-border/20 dark:border-warning-border/6',
      info: 'border-info-border/20 dark:border-info-border/6',
      success: 'border-success-border/20 dark:border-success-border/6',
      error: 'border-danger-border/20 dark:border-danger-border/6',
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
        default: 'text-foreground-muted/80',
        neutral: 'text-foreground-muted/80',
        warning: 'text-warning-text/80',
        info: 'text-info-text/80',
        success: 'text-success-text/80',
        error: 'text-danger-text/80',
        purple: 'text-decor-violet/80',
        violet: 'text-decor-violet/80',
      },
    },
  },
);
