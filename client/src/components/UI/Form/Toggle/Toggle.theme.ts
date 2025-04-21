import { cva } from 'class-variance-authority';

export const ToggleTheme = cva(
  `toggle theme-controller text-toggle-default checked:text-toggle-checked!`,
  {
    variants: {
      variant: {
        default:
          '[--tglbg:rgb(var(--color-toggle-default))] checked:[--tglbg:rgb(var(--color-toggle-default-checked))] bg-toggle-default checked:bg-toggle-default-checked hover:bg-toggle-hover-default hover:checked:bg-toggle-hover-default-checked border-bc-toggle-default checked:border-bc-toggle-default-checked',
        primary:
          '[--tglbg:rgb(var(--color-toggle-primary))] checked:[--tglbg:rgb(var(--color-toggle-primary-checked))] bg-toggle-primary checked:bg-toggle-primary-checked hover:bg-toggle-hover-primary hover:checked:bg-toggle-hover-primary-checked border-bc-toggle-primary checked:border-bc-toggle-primary-checked',
      },
      size: {
        xs: 'toggle-xs',
        sm: 'toggle-sm',
        md: 'toggle-md',
        lg: 'toggle-lg',
      },
    },
  },
);
