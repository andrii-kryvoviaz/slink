import { cva } from 'class-variance-authority';

export const BannerTheme = cva(
  'mb-6 p-4 rounded-xl border shadow-sm transition-all duration-200',
  {
    variants: {
      variant: {
        default:
          'bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900 border-slate-200/50 dark:border-slate-700/50',
        neutral:
          'bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 border-gray-200/50 dark:border-gray-600/50 shadow-gray-500/5',
        warning:
          'bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-amber-200/50 dark:border-amber-700/30 shadow-amber-500/10',
        info: 'bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-200/50 dark:border-blue-700/30 shadow-blue-500/10',
        success:
          'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200/50 dark:border-green-700/30 shadow-green-500/10',
        error:
          'bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-red-200/50 dark:border-red-700/30 shadow-red-500/10',
      },
    },
  },
);

export const BannerIconTheme = cva(
  'flex items-center justify-center w-10 h-10 min-w-[2.5rem] min-h-[2.5rem] rounded-lg shadow-sm flex-shrink-0',
  {
    variants: {
      variant: {
        default: 'bg-slate-900 dark:bg-white',
        neutral: 'bg-gray-600 dark:bg-gray-400',
        warning:
          'bg-gradient-to-br from-amber-500 to-orange-500 shadow-amber-500/30',
        info: 'bg-gradient-to-br from-blue-500 to-indigo-500 shadow-blue-500/30',
        success:
          'bg-gradient-to-br from-green-500 to-emerald-500 shadow-green-500/30',
        error: 'bg-gradient-to-br from-red-500 to-rose-500 shadow-red-500/30',
      },
    },
  },
);

export const BannerIconColorTheme = cva('h-5 w-5', {
  variants: {
    variant: {
      default: 'text-white dark:text-slate-900',
      neutral: 'text-white dark:text-gray-900',
      warning: 'text-white',
      info: 'text-white',
      success: 'text-white',
      error: 'text-white',
    },
  },
});

export const BannerActionTheme = cva('border transition-colors duration-200', {
  variants: {
    variant: {
      default:
        'bg-slate-100/80 hover:bg-slate-200/80 dark:bg-slate-700/80 dark:hover:bg-slate-600/80 border-slate-200 hover:border-slate-300 dark:border-slate-600 dark:hover:border-slate-500 text-slate-700 hover:text-slate-800 dark:text-slate-300 dark:hover:text-slate-200',
      neutral:
        'bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-700/80 dark:hover:bg-gray-600/80 border-gray-200 hover:border-gray-300 dark:border-gray-600 dark:hover:border-gray-500 text-gray-700 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200',
      warning:
        'bg-amber-100/90 hover:bg-amber-200/90 border-amber-400 hover:border-amber-500 text-amber-800 hover:text-amber-900 dark:bg-amber-800/40 dark:hover:bg-amber-700/60 dark:border-amber-600 dark:hover:border-amber-500 dark:text-amber-200 dark:hover:text-amber-100',
      info: 'bg-blue-100/80 hover:bg-blue-200/80 dark:bg-blue-800/40 dark:hover:bg-blue-700/60 border-blue-200 hover:border-blue-300 dark:border-blue-600 dark:hover:border-blue-500 text-blue-700 hover:text-blue-800 dark:text-blue-200 dark:hover:text-blue-100',
      success:
        'bg-green-100/80 hover:bg-green-200/80 dark:bg-green-800/40 dark:hover:bg-green-700/60 border-green-200 hover:border-green-300 dark:border-green-600 dark:hover:border-green-500 text-green-700 hover:text-green-800 dark:text-green-200 dark:hover:text-green-100',
      error:
        'bg-red-100/80 hover:bg-red-200/80 dark:bg-red-800/40 dark:hover:bg-red-700/60 border-red-200 hover:border-red-300 dark:border-red-600 dark:hover:border-red-500 text-red-700 hover:text-red-800 dark:text-red-200 dark:hover:text-red-100',
    },
  },
});
