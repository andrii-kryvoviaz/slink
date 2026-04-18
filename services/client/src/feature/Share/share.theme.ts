import { tv } from 'tailwind-variants';

export type ShareStatusKind = 'saving' | 'saved' | 'error';

export const status = tv({
  slots: {
    line: 'inline-flex items-center gap-1 text-xs',
    icon: 'h-3.5 w-3.5 shrink-0',
  },
  variants: {
    kind: {
      saving: { line: 'text-gray-500 dark:text-gray-400' },
      saved: { line: 'text-emerald-600 dark:text-emerald-400' },
      error: { line: 'text-red-600 dark:text-red-400' },
    },
    spinning: {
      true: { icon: 'animate-spin' },
    },
  },
});

export const statusIconName = (kind: ShareStatusKind): string => {
  if (kind === 'saving') {
    return 'ph:spinner';
  }

  if (kind === 'saved') {
    return 'ph:check-circle';
  }

  return 'ph:warning';
};
