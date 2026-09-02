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
          'text-foreground',
          'placeholder:text-foreground-subtle',
          'bg-muted/50',
          'border border-border/50',
          'focus:ring-2 focus:ring-ring/20 focus:border-border/50',
          'hover:bg-muted/70',
          'transition-all duration-200',
        ],
        input: [
          'border border-border',
          'bg-background dark:bg-input/30',
          'ring-offset-background',
          'placeholder:text-foreground-muted',
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
        class:
          'border-danger-border dark:border-danger-border/30 focus:ring-danger/20',
      },
      {
        variant: 'input',
        hasError: true,
        class: 'border-danger ring-danger/20 dark:ring-danger/40',
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
          'text-foreground-muted',
          'hover:text-foreground-soft',
          'hover:bg-hover',
          'active:bg-muted',
        ],
        input: [
          'text-foreground-muted',
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
