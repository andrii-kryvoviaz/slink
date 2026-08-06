import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const commentListItemTheme = cva(
  'group flex gap-3 p-2 transition-colors',
  {
    variants: {
      deleted: {
        true: 'opacity-50',
        false: '',
      },
      editing: {
        true: 'bg-surface-inverse-foreground/5 border-l-2 border-surface-inverse-foreground/40',
        false: 'hover:bg-surface-inverse-foreground/5 rounded-lg',
      },
    },
    defaultVariants: {
      deleted: false,
      editing: false,
    },
  },
);

export type CommentListItemVariants = VariantProps<typeof commentListItemTheme>;
