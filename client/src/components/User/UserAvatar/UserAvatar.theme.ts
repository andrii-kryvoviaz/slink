import { cva } from 'class-variance-authority';

export const UserAvatarTheme = cva(`flex-shrink-0 rounded-full object-cover`, {
  variants: {
    variant: {
      default: '',
      ring: 'ring ring-indigo-300 dark:ring-indigo-500',
    },
    size: {
      sm: 'w-8 h-8',
      md: 'w-12 h-12',
      lg: 'w-16 h-16',
      xl: 'w-24 h-24',
    },
  },
});
