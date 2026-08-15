import { tv } from 'tailwind-variants';

export type ShareStatusKind = 'saving' | 'saved' | 'error';

export const status = tv({
  slots: {
    line: 'inline-flex items-center gap-1 text-xs',
    icon: 'h-3.5 w-3.5 shrink-0',
  },
  variants: {
    kind: {
      saving: { line: 'text-foreground-muted' },
      saved: { line: 'text-success-text' },
      error: { line: 'text-danger' },
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
