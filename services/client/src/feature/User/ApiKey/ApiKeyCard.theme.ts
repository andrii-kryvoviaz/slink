import { cva } from 'class-variance-authority';

export const apiKeyIconContainerVariants = cva(
  'shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
  {
    variants: {
      status: {
        expired: 'bg-red-50 dark:bg-red-900/20',
        active: 'bg-amber-50 dark:bg-amber-900/20',
        permanent: 'bg-emerald-50 dark:bg-emerald-900/20',
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
      expired: 'text-red-500 dark:text-red-400',
      active: 'text-amber-500 dark:text-amber-400',
      permanent: 'text-emerald-500 dark:text-emerald-400',
    },
  },
  defaultVariants: {
    status: 'permanent',
  },
});

export type ApiKeyStatus = 'expired' | 'active' | 'permanent';

export const statusLabels: Record<ApiKeyStatus, string> = {
  expired: 'Expired',
  active: 'Active',
  permanent: 'Permanent',
};
