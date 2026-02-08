<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { PickerColor } from './picker.theme';

  interface Props {
    icon?: Snippet;
    message?: Snippet;
    action?: Snippet;
    color?: PickerColor;
    onAction?: () => void;
  }

  let { icon, message, action, color = 'blue', onAction }: Props = $props();

  const actionColorClasses = $derived(
    color === 'blue'
      ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20'
      : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20',
  );
</script>

{#snippet defaultIcon()}
  <Icon
    icon="ph:list-dashes"
    class="w-5 h-5 text-gray-400 dark:text-gray-500"
  />
{/snippet}

{#snippet defaultMessage()}
  No items yet
{/snippet}

<div class="flex flex-col items-center gap-3 py-6 px-4">
  <div
    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
  >
    {@render (icon ?? defaultIcon)()}
  </div>
  <p class="text-sm text-gray-500 dark:text-gray-400">
    {@render (message ?? defaultMessage)()}
  </p>
  {#if onAction && action}
    <button
      type="button"
      onclick={onAction}
      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {actionColorClasses}"
    >
      <Icon icon="ph:plus" class="w-3 h-3" />
      {@render action()}
    </button>
  {/if}
</div>
