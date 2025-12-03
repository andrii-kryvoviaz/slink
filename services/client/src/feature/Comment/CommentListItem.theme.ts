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
        true: 'bg-white/5 border-l-2 border-white/40',
        false: 'hover:bg-white/5 rounded-lg',
      },
    },
    defaultVariants: {
      deleted: false,
      editing: false,
    },
  },
);

export type CommentListItemVariants = VariantProps<typeof commentListItemTheme>;
