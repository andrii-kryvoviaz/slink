import { cva } from 'class-variance-authority';

export const CardTheme = cva(`flex flex-col border w-full`, {
  variants: {
    variant: {
      gray: 'bg-muted text-foreground-soft border-border-strong divide-border-strong',
      red: 'bg-danger-wash dark:bg-danger-wash/20 text-danger-text border-danger-border dark:border-danger-border/30 divide-danger-border dark:divide-danger-border/30',
      yellow:
        'bg-decor-yellow-wash dark:bg-decor-yellow-wash/20 text-decor-yellow-text border-decor-yellow-border divide-decor-yellow-border',
      green:
        'bg-success-wash dark:bg-success-wash/20 text-success-text border-success-border dark:border-success-border/30 divide-success-border dark:divide-success-border/30',
      indigo: 'bg-accent/10 text-accent border-accent/40 divide-accent/40',
      purple: 'bg-accent/10 text-accent border-accent/40 divide-accent/40',
      pink: 'bg-decor-pink-wash dark:bg-decor-pink-wash/20 text-decor-pink-text border-decor-pink-border divide-decor-pink-border',
      blue: 'bg-info-wash dark:bg-info-wash/20 text-info-text border-info-border dark:border-info-border/30 divide-info-border dark:divide-info-border/30',
      light:
        'bg-surface-raised text-foreground-soft border-border-bold divide-border-bold',
      dark: 'bg-muted text-foreground-soft border-border-bold divide-border-bold',
      default:
        'bg-card dark:bg-card/60 text-foreground-muted border-border divide-border',
      enhanced:
        'bg-card dark:bg-card/60 text-foreground-muted border-border divide-border',
      dropdown:
        'bg-surface-raised text-foreground-soft border-border divide-border',
      navbar: 'bg-card text-foreground-soft border-border divide-border',
      navbarUl: 'bg-muted text-foreground-soft border-border divide-border',
      form: 'bg-surface-raised text-foreground border-border-strong divide-border-strong',
      primary:
        'bg-foreground-solid/5 text-foreground-solid border-foreground-solid/30 divide-foreground-solid/30',
      orange:
        'bg-decor-orange-wash dark:bg-decor-orange-wash/20 text-decor-orange-text border-decor-orange-border divide-decor-orange-border',
      none: '',
    },
    size: {
      xs: 'p-2 max-w-xs',
      sm: 'p-4 max-w-sm',
      md: 'p-4 sm:p-5 max-w-xl',
      lg: 'p-4 sm:p-6 max-w-2xl',
      xl: 'p-4 sm:p-8 max-w-(--breakpoint-xl)',
      full: 'p-4 sm:p-5 max-w-full',
    },
    rounded: {
      none: 'rounded-none',
      sm: 'rounded-sm',
      md: 'rounded-md',
      lg: 'rounded-lg',
      xl: 'rounded-xl',
      full: 'rounded-full',
    },
    shadow: {
      none: '',
      sm: 'shadow-xs',
      md: 'shadow-sm',
      lg: 'shadow-lg',
      xl: 'shadow-xl',
      '2xl': 'shadow-2xl',
      inner: 'shadow-inner',
    },
  },
});
