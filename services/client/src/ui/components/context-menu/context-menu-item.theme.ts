import { cva } from 'class-variance-authority';

export const contextMenuItemTheme = cva(
  'outline-hidden relative flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*="size-"])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default:
          'text-foreground-soft data-highlighted:bg-info-wash dark:data-highlighted:bg-info-wash/20 data-highlighted:text-info-text hover:bg-info-wash dark:hover:bg-info-wash/20 hover:text-info-text',
        destructive:
          'text-foreground-soft data-highlighted:bg-danger-wash dark:data-highlighted:bg-danger-wash/20 data-highlighted:text-danger-text hover:bg-danger-wash dark:hover:bg-danger-wash/20 hover:text-danger-text data-[variant=destructive]:*:[svg]:!text-danger-text',
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

export const contextMenuItemIconTheme = cva('transition-colors duration-150', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      destructive:
        'text-foreground-muted group-hover:text-danger-text group-data-[highlighted]:text-danger-text',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});
