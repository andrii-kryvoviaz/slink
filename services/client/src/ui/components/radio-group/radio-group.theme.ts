import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const RadioGroupCardTheme = cva(
  [
    'group w-full cursor-pointer text-left',
    'flex items-start gap-3 rounded-lg border p-3',
    'outline-none transition-colors duration-150',
    'focus-visible:ring-2 focus-visible:ring-offset-1',
    'dark:focus-visible:ring-offset-gray-900',
    'disabled:cursor-not-allowed disabled:opacity-50',
    'data-[state=unchecked]:hover:bg-gray-50 dark:data-[state=unchecked]:hover:bg-gray-800/40',
    'data-[state=unchecked]:hover:border-gray-300 dark:data-[state=unchecked]:hover:border-gray-600',
  ],
  {
    variants: {
      tone: {
        default: [
          'border-gray-200/70 dark:border-gray-700/60',
          'focus-visible:ring-gray-400/40',
          'data-[state=checked]:border-gray-400 dark:data-[state=checked]:border-gray-500',
          'data-[state=checked]:bg-gray-100 dark:data-[state=checked]:bg-gray-800/70',
        ],
        danger: [
          'border-gray-200/70 dark:border-gray-700/60',
          'focus-visible:ring-red-500/40',
          'data-[state=checked]:border-red-200/40 dark:data-[state=checked]:border-red-800/30',
          'data-[state=checked]:bg-red-100 dark:data-[state=checked]:bg-red-900/30',
        ],
      },
    },
    defaultVariants: {
      tone: 'default',
    },
  },
);

export const RadioGroupCardIndicatorTheme = cva(
  [
    'mt-0.5 flex aspect-square size-4 shrink-0 items-center justify-center',
    'rounded-full border shadow-xs transition-colors',
  ],
  {
    variants: {
      tone: {
        default: [
          'border-gray-300 dark:border-gray-600',
          'group-data-[state=checked]:border-gray-900 dark:group-data-[state=checked]:border-white',
        ],
        danger: [
          'border-gray-300 dark:border-gray-600',
          'group-data-[state=checked]:border-red-600 dark:group-data-[state=checked]:border-red-400',
        ],
      },
    },
    defaultVariants: {
      tone: 'default',
    },
  },
);

export const RadioGroupCardDotTheme = cva('size-2 fill-current', {
  variants: {
    tone: {
      default: 'text-gray-900 dark:text-white',
      danger: 'text-red-600 dark:text-red-400',
    },
  },
  defaultVariants: {
    tone: 'default',
  },
});

export const RadioGroupCardBodyTheme = cva('min-w-0 flex-1 space-y-0.5');

export const RadioGroupCardTitleTheme = cva('block text-xs font-medium', {
  variants: {
    tone: {
      default: 'text-gray-900 dark:text-white',
      danger: [
        'text-gray-900 dark:text-white',
        'group-data-[state=checked]:text-red-600 dark:group-data-[state=checked]:text-red-400',
      ],
    },
  },
  defaultVariants: {
    tone: 'default',
  },
});

export const RadioGroupCardDescriptionTheme = cva(
  'block text-xs text-gray-500 dark:text-gray-400',
);

export type RadioGroupCardTone = NonNullable<
  VariantProps<typeof RadioGroupCardTheme>['tone']
>;
