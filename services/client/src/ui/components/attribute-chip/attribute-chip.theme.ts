import { type VariantProps, tv } from 'tailwind-variants';

export const attributeChip = tv({
  slots: {
    root: 'inline-flex items-center rounded-full text-xs font-medium transition-colors',
    body: 'relative inline-flex h-7 min-w-0 cursor-pointer items-center gap-1.5 rounded-full outline-none transition-colors before:absolute before:inset-x-0 before:-inset-y-2 before:content-[""] focus-visible:ring-2 focus-visible:ring-indigo-500/40 [&_img]:size-4 [&_img]:shrink-0',
    plusIcon: 'h-3.5 w-3.5 shrink-0',
    label: 'truncate leading-none',
    remove:
      'relative mr-1 inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full p-1 outline-none transition-colors after:absolute after:-inset-y-2 after:inset-x-0 after:content-[""] hover:bg-black/10 focus-visible:ring-2 focus-visible:ring-indigo-500/40 dark:hover:bg-white/10',
    removeIcon: 'h-3 w-3',
  },
  variants: {
    state: {
      ghost: {
        root: 'border border-dashed border-gray-300 text-gray-500 hover:border-gray-400 hover:bg-gray-50 hover:text-gray-700 dark:border-gray-600 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-200',
        body: 'px-3',
      },
      set: {
        root: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        body: 'pl-2.5 pr-1.5 hover:text-indigo-900 dark:hover:text-indigo-100',
      },
    },
    disabled: {
      true: {
        root: 'pointer-events-none opacity-50',
      },
      false: {},
    },
  },
  defaultVariants: {
    state: 'ghost',
    disabled: false,
  },
});

export type AttributeChipVariants = VariantProps<typeof attributeChip>;
export type AttributeChipState = NonNullable<AttributeChipVariants['state']>;
