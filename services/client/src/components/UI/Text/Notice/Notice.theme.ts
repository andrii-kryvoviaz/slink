import { cva } from 'class-variance-authority';

export const NoticeTheme = cva(`border-l-4`, {
  variants: {
    variant: {
      info: 'bg-blue-50 border-blue-400 text-blue-700',
      success: 'bg-green-50 border-green-400 text-green-700',
      warning: 'bg-yellow-50 border-yellow-400 text-yellow-700',
      error: 'bg-red-50 border-red-400 text-red-700',
    },
    size: {
      xs: 'text-xs p-2',
      sm: 'text-sm p-4',
      md: 'text-base p-6',
      lg: 'text-lg p-8',
    },
  },
});
