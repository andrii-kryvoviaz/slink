import { cva } from 'class-variance-authority';

export const ToggleTheme = cva(
  `toggle theme-controller text-toggle-default checked:text-toggle-checked`,
  {
    variants: {
      variant: {
        default:
          '[--tglbg:rgb(var(--bg-toggle-default))] checked:[--tglbg:rgb(var(--bg-toggle-default-checked))] bg-toggle-default checked:bg-toggle-default-checked hover:bg-toggle-hover-default hover:checked:bg-toggle-hover-default-checked border-toggle-default checked:border-toggle-default-checked',
        primary:
          '[--tglbg:rgb(var(--bg-toggle-primary))] checked:[--tglbg:rgb(var(--bg-toggle-primary-checked))] bg-toggle-primary checked:bg-toggle-primary-checked hover:bg-toggle-hover-primary hover:checked:bg-toggle-hover-primary-checked border-toggle-primary checked:border-toggle-primary-checked',
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
