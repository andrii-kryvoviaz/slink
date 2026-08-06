import { cva } from 'class-variance-authority';

export const dropdownMenuItemTheme = cva(
  'outline-hidden relative flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*="size-"])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default:
          'text-foreground-soft data-highlighted:bg-info-subtle data-highlighted:text-info-subtle-foreground hover:bg-info-subtle hover:text-info-subtle-foreground',
        destructive:
          'text-foreground-soft data-highlighted:bg-danger-subtle data-highlighted:text-danger-subtle-foreground hover:bg-danger-subtle hover:text-danger-subtle-foreground data-[variant=destructive]:*:[svg]:!text-danger-subtle-foreground',
      },
      inset: {
        true: 'data-[inset]:pl-8',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      inset: false,
    },
  },
);

export const dropdownMenuItemIconTheme = cva('transition-colors duration-150', {
  variants: {
    variant: {
      default: 'text-muted-foreground',
      destructive:
        'text-muted-foreground group-hover:text-danger-subtle-foreground group-data-[highlighted]:text-danger-subtle-foreground',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});
