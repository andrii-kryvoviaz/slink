import { cva } from 'class-variance-authority';

export const licenseInfoContainerTheme = cva(
  'inline-flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-card/95 border-border-strong/50 dark:bg-scrim/60 dark:border-border/50 backdrop-blur-md shadow-lg border',
        badge:
          'gap-1.5 px-2 py-1 rounded-lg bg-surface-inverse-foreground/10 hover:bg-surface-inverse-foreground/20 backdrop-blur-sm',
        inline: 'gap-2',
        text: '',
      },
    },
    defaultVariants: {
      variant: 'overlay',
    },
  },
);

export const licenseInfoIconTheme = cva('shrink-0', {
  variants: {
    variant: {
      overlay: 'h-3 w-3 text-foreground-soft',
      badge: '',
      inline: 'text-muted-foreground mt-0.5',
      text: '',
    },
    size: {
      sm: 'w-3 h-3',
      md: 'w-4 h-4',
      lg: 'w-5 h-5',
    },
  },
  compoundVariants: [
    {
      variant: 'overlay',
      class: 'w-3 h-3',
    },
  ],
  defaultVariants: {
    variant: 'overlay',
    size: 'md',
  },
});

export const licenseInfoLabelTheme = cva('font-medium', {
  variants: {
    variant: {
      overlay: 'text-[11px] text-foreground-soft',
      badge: '',
      inline: 'text-foreground',
      text: '',
    },
    size: {
      sm: 'text-xs',
      md: 'text-sm',
      lg: 'text-base',
    },
  },
  compoundVariants: [
    {
      variant: 'overlay',
      class: 'text-[11px]',
    },
  ],
  defaultVariants: {
    variant: 'overlay',
    size: 'md',
  },
});

export const licenseInfoPopoverTheme = cva('rounded-xl shadow-xl', {
  variants: {
    variant: {
      overlay: '',
      badge:
        'w-80 p-4 bg-surface-inverse border border-surface-inverse-foreground/20',
      inline: 'w-80 p-4 bg-input border border-border',
      text: '',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export type LicenseInfoVariant = 'overlay' | 'badge' | 'inline' | 'text';
export type LicenseInfoSize = 'sm' | 'md' | 'lg';
