import { tv } from 'tailwind-variants';

export const backLinkVariants = tv({
  slots: {
    base: 'group inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors duration-200',
    icon: 'w-4 h-4 transition-transform duration-200 group-hover:-translate-x-0.5',
  },
});
