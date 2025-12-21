import { cva } from 'class-variance-authority';

export const passwordStrengthBarVariants = cva(
  'h-1 flex-1 rounded-full transition-colors duration-200',
  {
    variants: {
      strength: {
        weak: 'bg-red-500',
        fair: 'bg-orange-500',
        good: 'bg-yellow-500',
        strong: 'bg-green-500',
        veryStrong: 'bg-emerald-500',
      },
      active: {
        true: '',
        false: 'bg-gray-200 dark:bg-gray-700',
      },
    },
    compoundVariants: [
      { active: false, class: 'bg-gray-200 dark:bg-gray-700' },
    ],
    defaultVariants: {
      strength: 'weak',
      active: false,
    },
  },
);

export const passwordStrengthLabelVariants = cva('text-xs font-medium', {
  variants: {
    strength: {
      weak: 'text-red-500',
      fair: 'text-orange-500',
      good: 'text-yellow-600 dark:text-yellow-500',
      strong: 'text-green-500',
      veryStrong: 'text-emerald-500',
    },
  },
  defaultVariants: {
    strength: 'weak',
  },
});

export type StrengthLevel = 'weak' | 'fair' | 'good' | 'strong' | 'veryStrong';

export const strengthLabels: Record<StrengthLevel, string> = {
  weak: 'Weak',
  fair: 'Fair',
  good: 'Good',
  strong: 'Strong',
  veryStrong: 'Very Strong',
};
