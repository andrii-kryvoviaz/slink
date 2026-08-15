<script lang="ts">
  import { Button, type ButtonVariant } from '@slink/ui/components/button';
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  const iconContainerVariants = cva(
    'flex h-10 w-10 items-center justify-center rounded-full shadow-sm shrink-0 border',
    {
      variants: {
        variant: {
          blue: 'bg-info-solid/15 border-info-border/40 dark:border-info-border/12',
          danger:
            'bg-danger/15 border-danger-border/40 dark:border-danger-border/12',
        },
      },
    },
  );

  const iconVariants = cva('h-5 w-5', {
    variants: {
      variant: {
        blue: 'text-info',
        danger: 'text-danger',
      },
    },
  });

  interface Props {
    count: number;
    icon: string;
    title: Snippet;
    description?: Snippet;
    variant?: 'blue' | 'danger';
    loading?: boolean;
    confirmText?: Snippet;
    confirmVariant?: ButtonVariant;
    onConfirm?: () => void;
    onCancel: () => void;
    children?: Snippet;
    actions?: Snippet;
  }

  let {
    count,
    icon,
    title,
    description,
    variant = 'blue',
    loading = false,
    confirmText,
    confirmVariant = 'default',
    onConfirm,
    onCancel,
    children,
    actions,
  }: Props = $props();
</script>

<div class="w-xs max-w-screen space-y-4">
  <div class="flex items-center gap-3">
    <div class={iconContainerVariants({ variant })}>
      <Icon {icon} class={iconVariants({ variant })} />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">
        {@render title()} ({plural(count, ['# image', '# images'])})
      </h3>
      {#if description}
        <p class="text-xs text-foreground-muted">
          {@render description()}
        </p>
      {/if}
    </div>
  </div>

  {#if children}
    {@render children()}
  {/if}

  <div class="flex gap-3 pt-2">
    {#if actions}
      {@render actions()}
    {:else if confirmText && onConfirm}
      <Button
        variant="glass"
        rounded="full"
        size="sm"
        onclick={onCancel}
        class="flex-1"
        disabled={loading}
      >
        Cancel
      </Button>
      <Button
        variant={confirmVariant}
        rounded="full"
        size="sm"
        onclick={onConfirm}
        justify="center"
        class="flex-1 gap-1.5 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
        {loading}
      >
        {@render confirmText()}
      </Button>
    {:else}
      <Button
        variant="glass"
        rounded="full"
        size="sm"
        onclick={onCancel}
        class="flex-1"
        disabled={loading}
      >
        Cancel
      </Button>
    {/if}
  </div>
</div>
