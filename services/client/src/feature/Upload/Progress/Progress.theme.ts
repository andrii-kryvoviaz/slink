import { cva } from 'class-variance-authority';

export const UploadProgressNumberTheme = cva('font-light tracking-tight', {
  variants: {
    size: {
      sm: 'text-3xl',
      md: 'text-4xl sm:text-5xl',
      lg: 'text-5xl sm:text-6xl',
    },
    shimmer: {
      true: 'bg-gradient-to-r from-foreground-soft via-ring to-foreground-soft bg-[length:200%_100%] animate-shimmer bg-clip-text text-transparent',
      false: 'text-foreground',
    },
    animated: {
      true: 'tabular-nums',
      false: '',
    },
  },
  defaultVariants: {
    size: 'sm',
    animated: false,
    shimmer: false,
  },
});

export const UploadProgressStatusIconTheme = cva('w-5 h-5', {
  variants: {
    status: {
      completed: 'text-foreground-soft',
      error: 'text-danger',
      cancelled: 'text-foreground-subtle',
      pending: 'text-border-strong',
    },
  },
});
