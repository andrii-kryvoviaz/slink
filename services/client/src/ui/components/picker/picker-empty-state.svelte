<script lang="ts">
  import { Button } from '@slink/ui/components/button';
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

<div class="flex flex-col items-center gap-2 py-4 px-4">
  <div
    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
  >
    {@render (icon ?? defaultIcon)()}
  </div>
  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
    {@render (message ?? defaultMessage)()}
  </p>
  {#if onAction && action}
    <Button
      variant={color === 'blue' ? 'soft-blue' : 'soft-indigo'}
      size="xs"
      rounded="full"
      onclick={onAction}
    >
      <Icon icon="ph:plus" class="w-3 h-3" />
      {@render action()}
    </Button>
  {/if}
</div>
