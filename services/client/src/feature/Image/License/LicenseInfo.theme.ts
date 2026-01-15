import { cva } from 'class-variance-authority';

export const licenseInfoContainerTheme = cva(
  'inline-flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-white/95 border-gray-300/50 dark:bg-black/60 backdrop-blur-md shadow-lg border dark:border-white/10',
        badge:
          'gap-1.5 px-2 py-1 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm',
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
      overlay: 'h-3 w-3 text-gray-600 dark:text-gray-300',
      badge: '',
      inline: 'text-slate-500 dark:text-slate-400 mt-0.5',
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
      overlay: 'text-[11px] text-gray-700 dark:text-gray-200',
      badge: '',
      inline: 'text-slate-900 dark:text-slate-100',
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
      badge: 'w-80 p-4 bg-slate-900 border border-slate-700',
      inline:
        'w-80 p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700',
      text: '',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export type LicenseInfoVariant = 'overlay' | 'badge' | 'inline' | 'text';
export type LicenseInfoSize = 'sm' | 'md' | 'lg';
