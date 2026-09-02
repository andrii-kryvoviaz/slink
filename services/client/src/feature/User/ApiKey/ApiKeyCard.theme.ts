import { cva } from 'class-variance-authority';

export const apiKeyIconContainerVariants = cva(
  'shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
  {
    variants: {
      status: {
        expired: 'bg-danger/8',
        active: 'bg-warning/8',
        permanent: 'bg-success/8',
      },
    },
    defaultVariants: {
      status: 'permanent',
    },
  },
);

export const apiKeyIconVariants = cva('w-5 h-5', {
  variants: {
    status: {
      expired: 'text-danger',
      active: 'text-warning',
      permanent: 'text-success-strong',
    },
  },
  defaultVariants: {
    status: 'permanent',
  },
});

export type ApiKeyStatus = 'expired' | 'active' | 'permanent';
