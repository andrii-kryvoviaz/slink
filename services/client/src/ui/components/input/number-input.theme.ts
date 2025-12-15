import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const numberInputContainerVariants = cva(
  'relative inline-flex items-center w-full',
  {
    variants: {
      size: {
        xs: 'h-7',
        sm: 'h-8',
        md: 'h-9',
        lg: 'h-10',
        xl: 'h-11',
      },
      disabled: {
        true: 'opacity-50 pointer-events-none',
        false: '',
      },
    },
    defaultVariants: {
      size: 'md',
      disabled: false,
    },
  },
);

export const numberInputFieldVariants = cva(
  [
    'w-full h-full',
    'focus:outline-none',
    'tabular-nums',
    '[appearance:textfield]',
    '[&::-webkit-outer-spin-button]:appearance-none',
    '[&::-webkit-inner-spin-button]:appearance-none',
  ],
  {
    variants: {
      variant: {
        default: [
          'text-gray-900 dark:text-gray-100',
          'placeholder:text-gray-400 dark:placeholder:text-gray-500',
          'bg-gray-50/80 dark:bg-gray-800/50',
          'border border-gray-200/50 dark:border-gray-700/30',
          'focus:ring-2 focus:ring-blue-500/20 focus:border-gray-200/50 dark:focus:border-gray-700/30',
          'hover:bg-gray-100/50 dark:hover:bg-gray-800/70',
          'transition-all duration-200',
        ],
        input: [
          'border border-border',
          'bg-background dark:bg-input/30',
          'ring-offset-background',
          'placeholder:text-muted-foreground',
          'shadow-xs',
          'text-base md:text-sm font-medium',
          'transition-[color,box-shadow]',
          'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
          'disabled:cursor-not-allowed disabled:opacity-50',
        ],
      },
      size: {
        xs: 'text-xs px-2 pr-5 rounded',
        sm: 'text-xs px-2.5 pr-6 rounded-md',
        md: 'text-sm px-3 pr-7 rounded-md',
        lg: 'text-sm px-3.5 pr-8 rounded-md',
        xl: 'px-4 pr-9 rounded-md',
      },
      hasError: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        hasError: true,
        class: 'border-red-300 dark:border-red-700/50 focus:ring-red-500/20',
      },
      {
        variant: 'input',
        hasError: true,
        class:
          'border-destructive ring-destructive/20 dark:ring-destructive/40',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
      hasError: false,
    },
  },
);

export const numberInputButtonGroupVariants = cva(
  ['absolute right-0 top-0 bottom-0', 'flex flex-col'],
  {
    variants: {
      variant: {
        default: '',
        input: '',
      },
      size: {
        xs: 'w-4',
        sm: 'w-5',
        md: 'w-6',
        lg: 'w-7',
        xl: 'w-8',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const numberInputButtonVariants = cva(
  [
    'flex-1 flex items-center justify-center',
    'transition-colors duration-150',
    'select-none cursor-pointer',
    'disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent',
  ],
  {
    variants: {
      variant: {
        default: [
          'text-gray-500 dark:text-gray-400',
          'hover:text-gray-700 dark:hover:text-gray-200',
          'hover:bg-gray-100 dark:hover:bg-gray-700/50',
          'active:bg-gray-200 dark:active:bg-gray-700',
        ],
        input: [
          'text-muted-foreground',
          'hover:text-foreground',
          'hover:bg-muted/50 dark:hover:bg-muted/30',
          'active:bg-muted/70 dark:active:bg-muted/50',
        ],
      },
      position: {
        top: '',
        bottom: '',
      },
      size: {
        xs: '[&>svg]:w-2.5 [&>svg]:h-2.5',
        sm: '[&>svg]:w-3 [&>svg]:h-3',
        md: '[&>svg]:w-3.5 [&>svg]:h-3.5',
        lg: '[&>svg]:w-4 [&>svg]:h-4',
        xl: '[&>svg]:w-4 [&>svg]:h-4',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        position: 'top',
        class: 'rounded-tr-md',
      },
      {
        variant: 'default',
        position: 'bottom',
        class: 'rounded-br-md',
      },
      {
        variant: 'input',
        position: 'top',
        class: 'rounded-tr-md',
      },
      {
        variant: 'input',
        position: 'bottom',
        class: 'rounded-br-md',
      },
    ],
    defaultVariants: {
      variant: 'default',
      position: 'top',
      size: 'md',
    },
  },
);

export type NumberInputVariant = NonNullable<
  VariantProps<typeof numberInputFieldVariants>['variant']
>;

export type NumberInputSize = NonNullable<
  VariantProps<typeof numberInputContainerVariants>['size']
>;

export type NumberInputContainerVariants = VariantProps<
  typeof numberInputContainerVariants
>;

export type NumberInputFieldVariants = VariantProps<
  typeof numberInputFieldVariants
>;

export type NumberInputButtonVariants = VariantProps<
  typeof numberInputButtonVariants
>;
