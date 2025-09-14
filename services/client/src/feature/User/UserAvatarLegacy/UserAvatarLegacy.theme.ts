import { cva } from 'class-variance-authority';

export const UserAvatarThemeLegacy = cva(
  `shrink-0 rounded-full object-cover text-white shadow-sm flex items-center justify-center`,
  {
    variants: {
      variant: {
        default: '',
        ring: 'ring-3 ring-indigo-300 dark:ring-indigo-500',
      },
      size: {
        xs: 'w-5 h-5 text-[0.5rem]',
        sm: 'w-8 h-8 text-[0.75rem]',
        md: 'w-12 h-12 text-[1rem]',
        lg: 'w-16 h-16 text-[1.25rem]',
        xl: 'w-24 h-24 text-[1.5rem]',
      },
    },
  },
);
