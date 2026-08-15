<script lang="ts">
  import { type VariantProps, cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  const toastVariants = cva(
    'flex items-start gap-3 p-4 border shadow-md backdrop-blur-xl rounded-xl w-full sm:min-w-[380px] sm:w-auto',
    {
      variants: {
        variant: {
          accent:
            'text-accent-subtle-foreground border-accent-border/20 shadow-accent/4 bg-accent-subtle',
          success:
            'text-success-subtle-foreground border-success-border/20 shadow-success/4 bg-success-subtle',
          warning:
            'text-warning-subtle-foreground border-warning-border/20 shadow-warning/4 bg-warning-subtle',
          danger:
            'text-danger-subtle-foreground border-danger-border/20 shadow-danger/4 bg-danger-subtle',
          info: 'text-info-subtle-foreground border-info-border/20 shadow-info/4 bg-info-subtle',
        },
      },
      defaultVariants: {
        variant: 'accent',
      },
    },
  );

  const iconContainerVariants = cva(
    'flex h-8 w-8 items-center justify-center rounded-full shrink-0',
    {
      variants: {
        variant: {
          accent: 'bg-accent/15',
          success: 'bg-success/15',
          warning: 'bg-warning/15',
          danger: 'bg-danger/15',
          info: 'bg-info/15',
        },
      },
      defaultVariants: {
        variant: 'accent',
      },
    },
  );

  const iconVariants = cva('h-4 w-4', {
    variants: {
      variant: {
        accent: 'text-accent',
        success: 'text-success',
        warning: 'text-warning',
        danger: 'text-danger',
        info: 'text-info',
      },
    },
    defaultVariants: {
      variant: 'accent',
    },
  });

  const closeButtonVariants = cva(
    'shrink-0 flex h-8 w-8 items-center justify-center rounded-full focus:outline-none focus:ring-2 transition-colors duration-200',
    {
      variants: {
        variant: {
          accent: 'text-accent hover:bg-accent/15 focus:ring-accent/20',
          success: 'text-success hover:bg-success/15 focus:ring-success/20',
          warning: 'text-warning hover:bg-warning/15 focus:ring-warning/20',
          danger: 'text-danger hover:bg-danger/15 focus:ring-danger/20',
          info: 'text-info hover:bg-info/15 focus:ring-info/20',
        },
      },
      defaultVariants: {
        variant: 'accent',
      },
    },
  );

  interface Props extends VariantProps<typeof toastVariants> {
    icon?: string;
    oncloseToast?: () => void;
    children: Snippet;
  }

  let {
    variant = 'accent',
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
