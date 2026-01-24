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
      ? 'text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300'
      : 'text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300',
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
      class="text-sm font-medium transition-colors {actionColorClasses}"
    >
      {@render action()}
    </button>
  {/if}
</div>
