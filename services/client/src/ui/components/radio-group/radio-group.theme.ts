import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const RadioGroupCardTheme = cva(
  [
    'group w-full cursor-pointer text-left',
    'flex items-start gap-3 rounded-lg border p-3',
    'outline-none transition-colors duration-150',
    'focus-visible:ring-2 focus-visible:ring-offset-1',
    'focus-visible:ring-offset-card',
    'disabled:cursor-not-allowed disabled:opacity-50',
    'data-[state=unchecked]:hover:bg-ghost-hover/40',
    'data-[state=unchecked]:hover:border-border-strong',
  ],
  {
    variants: {
      tone: {
        default: [
          'border-border/70',
          'focus-visible:ring-ring/40',
          'data-[state=checked]:border-ring',
          'data-[state=checked]:bg-muted',
        ],
        danger: [
          'border-border/70',
          'focus-visible:ring-danger/40',
          'data-[state=checked]:border-danger-border/40',
          'data-[state=checked]:bg-danger-subtle',
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
          'border-border-strong',
          'group-data-[state=checked]:border-foreground',
        ],
        danger: [
          'border-border-strong',
          'group-data-[state=checked]:border-danger',
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
      default: 'text-foreground',
      danger: 'text-danger',
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
      default: 'text-foreground',
      danger: ['text-foreground', 'group-data-[state=checked]:text-danger'],
    },
  },
  defaultVariants: {
    tone: 'default',
  },
});

export const RadioGroupCardDescriptionTheme = cva(
  'block text-xs text-muted-foreground',
);

export type RadioGroupCardTone = NonNullable<
  VariantProps<typeof RadioGroupCardTheme>['tone']
>;
