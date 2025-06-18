<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { quartOut } from 'svelte/easing';
  import { type Readable, readable } from 'svelte/store';
  import { scale } from 'svelte/transition';

  import { Button } from '@slink/components/UI/Action';

  export type ConfirmationVariant = 'danger' | 'warning' | 'info' | 'success';

  interface Props {
    variant?: ConfirmationVariant;
    icon?: string;
    title: string;
    message?: string;
    content?: Snippet;
    confirmText?: string;
    cancelText?: string;
    loading?: Readable<boolean>;
    close: () => void;
    confirm: () => void;
  }

  let {
    variant = 'danger',
    icon,
    title,
    message,
    content,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    loading = readable(false),
    close,
    confirm,
  }: Props = $props();

  const variantConfig = {
    danger: {
      icon: 'heroicons:exclamation-triangle',
      iconBg: 'bg-red-50 dark:bg-red-950/20',
      iconColor: 'text-red-600 dark:text-red-400',
      confirmVariant: 'danger' as const,
    },
    warning: {
      icon: 'heroicons:exclamation-triangle',
      iconBg: 'bg-yellow-50 dark:bg-yellow-950/20',
      iconColor: 'text-yellow-600 dark:text-yellow-400',
      confirmVariant: 'warning' as const,
    },
    info: {
      icon: 'heroicons:information-circle',
      iconBg: 'bg-blue-50 dark:bg-blue-950/20',
      iconColor: 'text-blue-600 dark:text-blue-400',
      confirmVariant: 'primary' as const,
    },
    success: {
      icon: 'heroicons:check-circle',
      iconBg: 'bg-green-50 dark:bg-green-950/20',
      iconColor: 'text-green-600 dark:text-green-400',
      confirmVariant: 'success' as const,
    },
  };

  const config = $derived(variantConfig[variant]);
  const displayIcon = $derived(icon || config.icon);
</script>

<div class="space-y-6">
  <div class="flex items-start gap-4">
    <div
      class="flex h-12 w-12 items-center justify-center rounded-full {config.iconBg}"
    >
      <Icon icon={displayIcon} class="h-6 w-6 {config.iconColor}" />
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {title}
        </h3>

        {#if $loading}
          <div
            class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-gray-600 dark:border-gray-600 dark:border-t-gray-300"
            in:scale={{ duration: 200, easing: quartOut }}
          ></div>
        {/if}
      </div>

      {#if message}
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          {message}
        </p>
      {/if}
    </div>
  </div>

  {#if content}
    <div>
      {@render content()}
    </div>
  {/if}

  <div class="flex gap-3 pt-2">
    <Button
      variant="outline"
      size="md"
      class="flex-1 font-medium"
      onclick={close}
      disabled={$loading}
    >
      {cancelText}
    </Button>

    <Button
      variant={config.confirmVariant}
      size="md"
      class="flex-1 font-medium"
      onclick={confirm}
      disabled={$loading}
    >
      {confirmText}
      {#if $loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 ml-2" />
      {/if}
    </Button>
  </div>
</div>
