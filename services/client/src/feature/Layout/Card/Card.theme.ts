import { cva } from 'class-variance-authority';

export const CardTheme = cva(`flex flex-col border w-full`, {
  variants: {
    variant: {
      gray: 'bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-800 divide-gray-300 dark:divide-gray-800',
      red: 'bg-red-50 dark:bg-gray-800 text-red-800 dark:text-red-400 border-red-300 dark:border-red-800 divide-red-300 dark:divide-red-800',
      yellow:
        'bg-yellow-50 dark:bg-gray-800 text-yellow-800 dark:text-yellow-300 border-yellow-300 dark:border-yellow-800 divide-yellow-300 dark:divide-yellow-800',
      green:
        'bg-green-50 dark:bg-gray-800 text-green-800 dark:text-green-400 border-green-300 dark:border-green-800 divide-green-300 dark:divide-green-800',
      indigo:
        'bg-indigo-50 dark:bg-gray-800 text-indigo-800 dark:text-indigo-400 border-indigo-300 dark:border-indigo-800 divide-indigo-300 dark:divide-indigo-800',
      purple:
        'bg-purple-50 dark:bg-gray-800 text-purple-800 dark:text-purple-400 border-purple-300 dark:border-purple-800 divide-purple-300 dark:divide-purple-800',
      pink: 'bg-pink-50 dark:bg-gray-800 text-pink-800 dark:text-pink-400 border-pink-300 dark:border-pink-800 divide-pink-300 dark:divide-pink-800',
      blue: 'bg-blue-50 dark:bg-gray-800 text-blue-800 dark:text-blue-400 border-blue-300 dark:border-blue-800 divide-blue-300 dark:divide-blue-800',
      light:
        'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-500 divide-gray-500',
      dark: 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-500 divide-gray-500',
      default:
        'bg-white dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-800 divide-gray-200 dark:divide-gray-800',
      enhanced:
        'bg-white dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-800 divide-gray-200 dark:divide-gray-800',
      dropdown:
        'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-100 dark:border-gray-600 divide-gray-100 dark:divide-gray-600',
      navbar:
        'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 border-gray-100 dark:border-gray-700 divide-gray-100 dark:divide-gray-700',
      navbarUl:
        'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-400 border-gray-100 dark:border-gray-700 divide-gray-100 dark:divide-gray-700',
      form: 'bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-700 divide-gray-300 dark:divide-gray-700',
      primary:
        'bg-primary-50 dark:bg-gray-800 text-primary-800 dark:text-primary-400 border-primary-500 dark:border-primary-200 divide-primary-500 dark:divide-primary-200',
      orange:
        'bg-orange-50 dark:bg-orange-800 text-orange-800 dark:text-orange-400 border-orange-300 dark:border-orange-800 divide-orange-300 dark:divide-orange-800',
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
