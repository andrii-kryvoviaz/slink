<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import {
    type ModalVariant,
    buttonVariantMap,
    modalHeaderIconContainerVariants,
    modalHeaderIconVariants,
  } from '@slink/ui/components/dialog/modal.theme';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  export type ConfirmationVariant = 'danger' | 'amber' | 'blue' | 'green';

  interface Props {
    variant?: ConfirmationVariant;
    icon?: string;
    title: string;
    message?: string;
    content?: Snippet;
    confirmText?: string;
    cancelText?: string;
    loading?: boolean;
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
    loading = false,
    close,
    confirm,
  }: Props = $props();

  const iconMap: Record<ConfirmationVariant, string> = {
    danger: 'heroicons:exclamation-triangle',
    amber: 'heroicons:exclamation-triangle',
    blue: 'heroicons:information-circle',
    green: 'heroicons:check-circle',
  };

  const displayIcon = $derived(icon || iconMap[variant]);
  const submitVariant = $derived(
    buttonVariantMap[variant as ModalVariant] as any,
  );
</script>

<div
  class="space-y-6 p-6 bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 border border-slate-200/50 dark:border-slate-700/30 rounded-2xl shadow-lg backdrop-blur-sm"
>
  <div class="flex items-start gap-6">
    <div
      class={modalHeaderIconContainerVariants({
        variant: variant as ModalVariant,
      })}
    >
      <Icon
        icon={displayIcon}
        class={modalHeaderIconVariants({ variant: variant as ModalVariant })}
      />
    </div>

    <div class="flex-1 min-w-0">
      <h3
        class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
      >
        {title}
      </h3>

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
      size="sm"
      rounded="full"
      onclick={close}
      class="flex-1"
      disabled={loading}
    >
      {cancelText}
    </Button>

    <Button
      variant={submitVariant}
      size="sm"
      rounded="full"
      class="flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200"
      onclick={confirm}
      disabled={loading}
      {loading}
    >
      {confirmText}
    </Button>
  </div>
</div>
