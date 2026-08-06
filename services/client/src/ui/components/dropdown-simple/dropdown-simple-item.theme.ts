import { cva } from 'class-variance-authority';

export const dropdownSimpleContentTheme = cva(
  'z-50 p-1 flex flex-col w-fit min-w-48 origin-top rounded-xl shadow-xl backdrop-blur-sm',
  {
    variants: {
      variant: {
        default:
          'bg-popover shadow-scrim/10 dark:shadow-scrim/25 border border-border/80',
        dark: 'bg-surface-inverse shadow-scrim/30 border border-surface-inverse-foreground/10',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const dropdownSimpleItemTheme = cva(
  'flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-all duration-150',
  {
    variants: {
      variant: {
        default: 'text-foreground-soft',
        dark: 'text-surface-inverse-foreground/80',
      },
      danger: {
        true: '',
        false: '',
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed pointer-events-none',
        false: '',
      },
      state: {
        normal: '',
        loading: 'opacity-70 pointer-events-none',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        danger: false,
        class: 'hover:bg-info-subtle hover:text-info-subtle-foreground',
      },
      {
        variant: 'default',
        danger: true,
        class: 'hover:bg-danger-subtle hover:text-danger-subtle-foreground',
      },
      {
        variant: 'dark',
        danger: false,
        class:
          'hover:bg-surface-inverse-foreground/10 hover:text-surface-inverse-foreground',
      },
      {
        variant: 'dark',
        danger: true,
        class: 'hover:bg-danger/20 hover:text-surface-inverse-danger',
      },
    ],
    defaultVariants: {
      variant: 'default',
      danger: false,
      disabled: false,
      state: 'normal',
    },
  },
);

export const dropdownSimpleItemIconTheme = cva(
  'shrink-0 w-4 h-4 flex items-center justify-center',
);

export const dropdownSimpleItemTextTheme = cva('flex-1 min-w-0 truncate');

export type DropdownSimpleContentVariant = NonNullable<
  Parameters<typeof dropdownSimpleContentTheme>[0]
>['variant'];

export type DropdownSimpleItemVariant = NonNullable<
  Parameters<typeof dropdownSimpleItemTheme>[0]
>['variant'];
