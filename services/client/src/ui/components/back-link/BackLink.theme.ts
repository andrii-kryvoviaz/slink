import { tv } from 'tailwind-variants';

export const backLinkVariants = tv({
  slots: {
    base: 'group inline-flex items-center gap-1.5 text-sm text-foreground-muted hover:text-foreground transition-colors duration-200',
    icon: 'w-4 h-4 transition-transform duration-200 group-hover:-translate-x-0.5',
  },
});
