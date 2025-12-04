import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagBadgeCloseButtonVariants = cva(
  'h-4 w-4 p-0 rounded-full ml-1 transition-colors flex items-center justify-center opacity-60 hover:opacity-100',
  {
    variants: {
      variant: {
        default:
          'text-slate-700 hover:text-slate-900 hover:bg-slate-500/20 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-400/20',
        blue: 'text-blue-700 hover:text-blue-900 hover:bg-blue-500/20 dark:text-blue-300 dark:hover:text-blue-100 dark:hover:bg-blue-400/20',
        emerald:
          'text-emerald-700 hover:text-emerald-900 hover:bg-emerald-500/20 dark:text-emerald-300 dark:hover:text-emerald-100 dark:hover:bg-emerald-400/20',
        slate:
          'text-slate-700 hover:text-slate-900 hover:bg-slate-500/20 dark:text-slate-300 dark:hover:text-slate-100 dark:hover:bg-slate-400/20',
        purple:
          'text-purple-700 hover:text-purple-900 hover:bg-purple-500/20 dark:text-purple-300 dark:hover:text-purple-100 dark:hover:bg-purple-400/20',
        amber:
          'text-amber-700 hover:text-amber-900 hover:bg-amber-500/20 dark:text-amber-300 dark:hover:text-amber-100 dark:hover:bg-amber-400/20',
        orange:
          'text-orange-700 hover:text-orange-900 hover:bg-orange-500/20 dark:text-orange-300 dark:hover:text-orange-100 dark:hover:bg-orange-400/20',
        red: 'text-red-700 hover:text-red-900 hover:bg-red-500/20 dark:text-red-300 dark:hover:text-red-100 dark:hover:bg-red-400/20',
        success:
          'text-teal-700 hover:text-teal-900 hover:bg-teal-500/20 dark:text-teal-300 dark:hover:text-teal-100 dark:hover:bg-teal-400/20',
        destructive:
          'text-red-700 hover:text-red-900 hover:bg-red-500/20 dark:text-red-300 dark:hover:text-red-100 dark:hover:bg-red-400/20',
        warning:
          'text-yellow-700 hover:text-yellow-900 hover:bg-yellow-500/20 dark:text-yellow-300 dark:hover:text-yellow-100 dark:hover:bg-yellow-400/20',
        info: 'text-cyan-700 hover:text-cyan-900 hover:bg-cyan-500/20 dark:text-cyan-300 dark:hover:text-cyan-100 dark:hover:bg-cyan-400/20',
        indigo:
          'text-indigo-700 hover:text-indigo-900 hover:bg-indigo-500/20 dark:text-indigo-300 dark:hover:text-indigo-100 dark:hover:bg-indigo-400/20',
        pink: 'text-pink-700 hover:text-pink-900 hover:bg-pink-500/20 dark:text-pink-300 dark:hover:text-pink-100 dark:hover:bg-pink-400/20',
        neutral:
          'text-gray-700 hover:text-gray-900 hover:bg-gray-500/20 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:bg-gray-400/20',
        gradient: 'text-white hover:text-white hover:bg-white/20',
        neon: 'text-blue-600 hover:text-blue-800 hover:bg-blue-500/20 dark:text-blue-300 dark:hover:text-blue-100 dark:hover:bg-blue-400/20',
        minimal:
          'text-slate-600 hover:text-slate-900 hover:bg-slate-500/20 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-400/20',
        glass: 'text-white/70 hover:text-white hover:bg-white/10',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export type TagBadgeCloseButtonVariants = VariantProps<
  typeof tagBadgeCloseButtonVariants
>;
