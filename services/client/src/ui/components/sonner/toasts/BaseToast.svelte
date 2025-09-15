<script lang="ts">
  import { type VariantProps, cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  const toastVariants = cva(
    'flex items-start gap-3 p-4 border shadow-md backdrop-blur-xl rounded-xl min-w-[350px]',
    {
      variants: {
        variant: {
          amber: [
            'bg-amber-25/50 text-amber-700 border-amber-200/20 shadow-amber-500/4',
            'dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/20 dark:shadow-amber-900/10',
          ],
          purple: [
            'bg-purple-25/50 text-purple-700 border-purple-200/20 shadow-purple-500/4',
            'dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/20 dark:shadow-purple-900/10',
          ],
          red: [
            'bg-red-25/50 text-red-700 border-red-200/20 shadow-red-500/4',
            'dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/20 dark:shadow-red-900/10',
          ],
          green: [
            'bg-green-25/50 text-green-700 border-green-200/20 shadow-green-500/4',
            'dark:bg-green-950/50 dark:text-green-200 dark:border-green-800/20 dark:shadow-green-900/10',
          ],
          blue: [
            'bg-blue-25/50 text-blue-700 border-blue-200/20 shadow-blue-500/4',
            'dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/20 dark:shadow-blue-900/10',
          ],
          yellow: [
            'bg-yellow-25/50 text-yellow-700 border-yellow-200/20 shadow-yellow-500/4',
            'dark:bg-yellow-950/50 dark:text-yellow-200 dark:border-yellow-800/20 dark:shadow-yellow-900/10',
          ],
        },
      },
      defaultVariants: {
        variant: 'purple',
      },
    },
  );

  const iconContainerVariants = cva(
    'flex h-8 w-8 items-center justify-center rounded-full shrink-0',
    {
      variants: {
        variant: {
          amber: 'bg-amber-100/60 dark:bg-amber-950/60',
          purple: 'bg-purple-100/60 dark:bg-purple-950/60',
          red: 'bg-red-100/60 dark:bg-red-950/60',
          green: 'bg-green-100/60 dark:bg-green-950/60',
          blue: 'bg-blue-100/60 dark:bg-blue-950/60',
          yellow: 'bg-yellow-100/60 dark:bg-yellow-950/60',
        },
      },
      defaultVariants: {
        variant: 'purple',
      },
    },
  );

  const iconVariants = cva('h-4 w-4', {
    variants: {
      variant: {
        amber: 'text-amber-600 dark:text-amber-400',
        purple: 'text-purple-600 dark:text-purple-400',
        red: 'text-red-600 dark:text-red-400',
        green: 'text-green-600 dark:text-green-400',
        blue: 'text-blue-600 dark:text-blue-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
      },
    },
    defaultVariants: {
      variant: 'purple',
    },
  });

  const closeButtonVariants = cva(
    'shrink-0 flex h-8 w-8 items-center justify-center rounded-full focus:outline-none focus:ring-2 transition-colors duration-200',
    {
      variants: {
        variant: {
          amber: [
            'text-amber-600 dark:text-amber-400 hover:bg-amber-100/60 dark:hover:bg-amber-950/60',
            'focus:ring-amber-500/20',
          ],
          purple: [
            'text-purple-600 dark:text-purple-400 hover:bg-purple-100/60 dark:hover:bg-purple-950/60',
            'focus:ring-purple-500/20',
          ],
          red: [
            'text-red-600 dark:text-red-400 hover:bg-red-100/60 dark:hover:bg-red-950/60',
            'focus:ring-red-500/20',
          ],
          green: [
            'text-green-600 dark:text-green-400 hover:bg-green-100/60 dark:hover:bg-green-950/60',
            'focus:ring-green-500/20',
          ],
          blue: [
            'text-blue-600 dark:text-blue-400 hover:bg-blue-100/60 dark:hover:bg-blue-950/60',
            'focus:ring-blue-500/20',
          ],
          yellow: [
            'text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100/60 dark:hover:bg-yellow-950/60',
            'focus:ring-yellow-500/20',
          ],
        },
      },
      defaultVariants: {
        variant: 'purple',
      },
    },
  );

  interface Props extends VariantProps<typeof toastVariants> {
    icon?: string;
    oncloseToast?: () => void;
    children: Snippet;
  }

  let {
    variant = 'purple',
    icon = 'clarity:block-line',
    oncloseToast,
    children,
  }: Props = $props();

  const handleClose = () => {
    oncloseToast?.();
  };
</script>

<div class={toastVariants({ variant })}>
  <div class={iconContainerVariants({ variant })}>
    <Icon {icon} class={iconVariants({ variant })} />
  </div>
  <div class="flex-1 min-w-0">
    {@render children()}
  </div>
  <button
    type="button"
    class={closeButtonVariants({ variant })}
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
