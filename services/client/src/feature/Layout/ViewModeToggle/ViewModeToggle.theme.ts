import { tv } from 'tailwind-variants';

export const viewModeSliderTheme = tv({
  slots: {
    root: 'inline-flex shrink-0 select-none',
    track:
      'relative isolate flex items-stretch bg-gradient-to-br from-slate-100 to-slate-200/60 dark:from-slate-800/60 dark:to-slate-700/40 border border-slate-200 dark:border-slate-700 rounded-full p-0.5',
    thumb:
      'absolute top-0.5 bottom-0.5 left-0.5 z-0 rounded-full bg-white dark:bg-slate-600 shadow-sm ring-1 ring-slate-200/60 dark:ring-slate-500/40 motion-safe:transition-transform motion-safe:duration-300 motion-safe:ease-out pointer-events-none',
    step: 'group relative z-10 flex items-center justify-center bg-transparent border-0 cursor-pointer rounded-full outline-none focus-visible:ring-2 focus-visible:ring-ring/60 disabled:cursor-not-allowed disabled:opacity-50 aria-checked:cursor-default',
    icon: 'shrink-0 motion-safe:transition-colors motion-safe:duration-200 text-slate-700 dark:text-slate-100',
    dot: 'block rounded-full bg-slate-400/60 dark:bg-slate-500/70 motion-safe:transition-[transform,background-color] motion-safe:duration-200 group-hover:scale-125 group-hover:bg-slate-500 dark:group-hover:bg-slate-300',
    disabled: 'pointer-events-none opacity-60',
  },
  variants: {
    size: {
      sm: {
        track: 'h-6',
        step: 'w-7',
        icon: 'w-3 h-3',
        dot: 'w-1 h-1',
      },
      md: {
        track: 'h-7',
        step: 'w-9',
        icon: 'w-3.5 h-3.5',
        dot: 'w-1.5 h-1.5',
      },
      lg: {
        track: 'h-8',
        step: 'w-10',
        icon: 'w-4 h-4',
        dot: 'w-1.5 h-1.5',
      },
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
