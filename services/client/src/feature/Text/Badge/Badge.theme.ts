import { cva } from 'class-variance-authority';

export const BadgeTheme = cva(
  'inline-flex items-center justify-center rounded-full border font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
  {
    variants: {
      variant: {
        default:
          'border-slate-500/20 bg-slate-500/10 text-slate-700 [a&]:hover:bg-slate-500/15 focus-visible:ring-slate-500/20 dark:border-slate-400/30 dark:bg-slate-400/10 dark:text-slate-300 dark:[a&]:hover:bg-slate-400/15',
        blue: 'border-blue-500/20 bg-blue-500/10 text-blue-700 [a&]:hover:bg-blue-500/15 focus-visible:ring-blue-500/20 dark:border-blue-400/30 dark:bg-blue-400/10 dark:text-blue-300 dark:[a&]:hover:bg-blue-400/15',
        emerald:
          'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 [a&]:hover:bg-emerald-500/15 focus-visible:ring-emerald-500/20 dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-300 dark:[a&]:hover:bg-emerald-400/15',
        slate:
          'border-slate-500/20 bg-slate-500/10 text-slate-700 [a&]:hover:bg-slate-500/15 focus-visible:ring-slate-500/20 dark:border-slate-400/30 dark:bg-slate-400/10 dark:text-slate-300 dark:[a&]:hover:bg-slate-400/15',
        purple:
          'border-purple-500/20 bg-purple-500/10 text-purple-700 [a&]:hover:bg-purple-500/15 focus-visible:ring-purple-500/20 dark:border-purple-400/30 dark:bg-purple-400/10 dark:text-purple-300 dark:[a&]:hover:bg-purple-400/15',
        amber:
          'border-amber-500/20 bg-amber-500/10 text-amber-700 [a&]:hover:bg-amber-500/15 focus-visible:ring-amber-500/20 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300 dark:[a&]:hover:bg-amber-400/15',
        orange:
          'border-orange-500/20 bg-orange-500/10 text-orange-700 [a&]:hover:bg-orange-500/15 focus-visible:ring-orange-500/20 dark:border-orange-400/30 dark:bg-orange-400/10 dark:text-orange-300 dark:[a&]:hover:bg-orange-400/15',
        red: 'border-red-500/20 bg-red-500/10 text-red-700 [a&]:hover:bg-red-500/15 focus-visible:ring-red-500/20 dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-300 dark:[a&]:hover:bg-red-400/15',
        success:
          'border-teal-500/20 bg-teal-500/10 text-teal-700 [a&]:hover:bg-teal-500/15 focus-visible:ring-teal-500/20 dark:border-teal-400/30 dark:bg-teal-400/10 dark:text-teal-300 dark:[a&]:hover:bg-teal-400/15',
        destructive:
          'border-red-500/20 bg-red-500/10 text-red-700 [a&]:hover:bg-red-500/15 focus-visible:ring-red-500/20 dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-300 dark:[a&]:hover:bg-red-400/15',
        warning:
          'border-yellow-500/20 bg-yellow-500/10 text-yellow-700 [a&]:hover:bg-yellow-500/15 focus-visible:ring-yellow-500/20 dark:border-yellow-400/30 dark:bg-yellow-400/10 dark:text-yellow-300 dark:[a&]:hover:bg-yellow-400/15',
        info: 'border-cyan-500/20 bg-cyan-500/10 text-cyan-700 [a&]:hover:bg-cyan-500/15 focus-visible:ring-cyan-500/20 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-300 dark:[a&]:hover:bg-cyan-400/15',
        indigo:
          'border-indigo-500/20 bg-indigo-500/10 text-indigo-700 [a&]:hover:bg-indigo-500/15 focus-visible:ring-indigo-500/20 dark:border-indigo-400/30 dark:bg-indigo-400/10 dark:text-indigo-300 dark:[a&]:hover:bg-indigo-400/15',
        pink: 'border-pink-500/20 bg-pink-500/10 text-pink-700 [a&]:hover:bg-pink-500/15 focus-visible:ring-pink-500/20 dark:border-pink-400/30 dark:bg-pink-400/10 dark:text-pink-300 dark:[a&]:hover:bg-pink-400/15',
        neutral:
          'border-gray-500/20 bg-gray-500/10 text-gray-700 [a&]:hover:bg-gray-500/15 focus-visible:ring-gray-500/20 dark:border-gray-400/30 dark:bg-gray-400/10 dark:text-gray-300 dark:[a&]:hover:bg-gray-400/15',
        gradient:
          'border-0 bg-gradient-to-br from-blue-500 to-purple-600 text-white font-semibold [a&]:hover:from-blue-600 [a&]:hover:to-purple-700 focus-visible:ring-blue-500/20',
        neon: [
          'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400',
          'border border-blue-500/20 dark:border-blue-400/30',
          'hover:from-blue-500/15 hover:to-purple-500/15',
          'focus-within:ring-2 focus-within:ring-blue-500/30 focus-within:ring-offset-2',
          'transition-all duration-200 cursor-pointer',
        ],
        minimal:
          'bg-slate-50 text-slate-600 border border-slate-100 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800',
      },
      size: {
        xs: 'px-2 py-0.5 text-xs',
        sm: 'px-2.5 py-1 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-3.5 py-1.5 text-sm',
      },
      outline: {
        true: 'bg-transparent border-2',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        class: 'rounded-lg',
      },
      {
        variant: 'minimal',
        class: 'rounded-lg',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);
