import { cva } from 'class-variance-authority';

export const CardTheme = cva(`flex flex-col border w-full`, {
  variants: {
    variant: {
      gray: 'bg-muted text-foreground-soft border-border-strong divide-border-strong',
      red: 'bg-danger-subtle text-danger-subtle-foreground border-danger-border divide-danger-border',
      yellow:
        'bg-decor-yellow-subtle text-decor-yellow-subtle-foreground border-decor-yellow-border divide-decor-yellow-border',
      green:
        'bg-success-subtle text-success-subtle-foreground border-success-border divide-success-border',
      indigo: 'bg-accent/10 text-accent border-accent/40 divide-accent/40',
      purple: 'bg-accent/10 text-accent border-accent/40 divide-accent/40',
      pink: 'bg-decor-pink-subtle text-decor-pink-subtle-foreground border-decor-pink-border divide-decor-pink-border',
      blue: 'bg-info-subtle text-info-subtle-foreground border-info-border divide-info-border',
      light:
        'bg-surface-raised text-foreground-soft border-border-stronger divide-border-stronger',
      dark: 'bg-muted text-foreground-soft border-border-stronger divide-border-stronger',
      default:
        'bg-card dark:bg-card/60 text-muted-foreground border-border divide-border',
      enhanced:
        'bg-card dark:bg-card/60 text-muted-foreground border-border divide-border',
      dropdown:
        'bg-surface-raised text-foreground-soft border-border divide-border',
      navbar: 'bg-card text-foreground-soft border-border divide-border',
      navbarUl: 'bg-muted text-foreground-soft border-border divide-border',
      form: 'bg-surface-raised text-foreground border-border-strong divide-border-strong',
      primary: 'bg-primary/5 text-primary border-primary/30 divide-primary/30',
      orange:
        'bg-decor-orange-subtle text-decor-orange-subtle-foreground border-decor-orange-border divide-decor-orange-border',
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
