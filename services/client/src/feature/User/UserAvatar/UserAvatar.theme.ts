import { cva } from 'class-variance-authority';

export const userAvatarVariants = cva('rounded-xl', {
  variants: {
    size: {
      xs: 'size-4',
      sm: 'size-6',
      md: 'size-8',
      lg: 'size-10',
      xl: 'size-12',
      '2xl': 'size-16',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});

export const userAvatarFallbackVariants = cva(
  'rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 text-white font-semibold flex items-center justify-center',
  {
    variants: {
      size: {
        xs: 'text-[0.7em]',
        sm: 'text-xs',
        md: 'text-xs',
        lg: 'text-sm',
        xl: 'text-base',
        '2xl': 'text-lg',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export type UserAvatarSize = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
