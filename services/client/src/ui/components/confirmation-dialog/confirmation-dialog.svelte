<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { quartOut } from 'svelte/easing';
  import { readable } from 'svelte/store';
  import type { Readable } from 'svelte/store';
  import { scale } from 'svelte/transition';

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
      iconBg:
        'bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-950/30 dark:to-red-900/20 border border-red-200/50 dark:border-red-800/30 shadow-sm',
      iconColor: 'text-red-600 dark:text-red-400',
      confirmVariant: 'danger' as const,
    },
    warning: {
      icon: 'heroicons:exclamation-triangle',
      iconBg:
        'bg-gradient-to-br from-amber-50 to-orange-100/50 dark:from-amber-950/30 dark:to-orange-900/20 border border-amber-200/50 dark:border-amber-800/30 shadow-sm',
      iconColor: 'text-amber-600 dark:text-amber-400',
      confirmVariant: 'warning' as const,
    },
    info: {
      icon: 'heroicons:information-circle',
      iconBg:
        'bg-gradient-to-br from-blue-50 to-indigo-100/50 dark:from-blue-950/30 dark:to-indigo-900/20 border border-blue-200/50 dark:border-blue-800/30 shadow-sm',
      iconColor: 'text-blue-600 dark:text-blue-400',
      confirmVariant: 'primary' as const,
    },
    success: {
      icon: 'heroicons:check-circle',
      iconBg:
        'bg-gradient-to-br from-emerald-50 to-green-100/50 dark:from-emerald-950/30 dark:to-green-900/20 border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm',
      iconColor: 'text-emerald-600 dark:text-emerald-400',
      confirmVariant: 'success' as const,
    },
  };

  const config = $derived(variantConfig[variant]);
  const displayIcon = $derived(icon || config.icon);
</script>

<div
  class="space-y-6 p-6 bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 border border-slate-200/50 dark:border-slate-700/30 rounded-2xl shadow-lg backdrop-blur-sm"
>
  <div class="flex items-start gap-6">
    <div
      class="flex h-14 w-14 items-center justify-center rounded-2xl {config.iconBg} backdrop-blur-sm"
    >
      <Icon icon={displayIcon} class="h-7 w-7 {config.iconColor}" />
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex items-center justify-between gap-4">
        <h3
          class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
        >
          {title}
        </h3>

        {#if $loading}
          <div in:scale={{ duration: 200, easing: quartOut }}>
            <Loader variant="minimal" size="sm" class="text-gray-400" />
          </div>
        {/if}
      </div>

      {#if message}
        <p
          class="mt-2 text-base text-slate-600 dark:text-slate-400 leading-relaxed"
        >
          {message}
        </p>
      {/if}
    </div>
  </div>

  {#if content}
    {@render content()}
  {/if}

  <div class="flex gap-4">
    <Button
      variant="glass"
      size="md"
      onclick={close}
      class="flex-1"
      disabled={$loading}
    >
      {cancelText}
    </Button>

    <Button
      variant={config.confirmVariant}
      size="md"
      class="flex-1 font-medium h-11 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
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
