import { cva } from 'class-variance-authority';

export const passwordStrengthBarVariants = cva(
  'h-1 flex-1 rounded-full transition-colors duration-200',
  {
    variants: {
      strength: {
        weak: 'bg-danger-fill',
        fair: 'bg-warning-strong',
        good: 'bg-warning',
        strong: 'bg-success',
        veryStrong: 'bg-success-strong',
      },
      active: {
        true: '',
        false: 'bg-surface-raised',
      },
    },
    compoundVariants: [{ active: false, class: 'bg-surface-raised' }],
    defaultVariants: {
      strength: 'weak',
      active: false,
    },
  },
);

export const passwordStrengthLabelVariants = cva('text-xs font-medium', {
  variants: {
    strength: {
      weak: 'text-danger',
      fair: 'text-warning-strong',
      good: 'text-warning',
      strong: 'text-success',
      veryStrong: 'text-success-strong',
    },
  },
  defaultVariants: {
    strength: 'weak',
  },
});

export type { StrengthLevel } from './PasswordStrength.labels.svelte';
