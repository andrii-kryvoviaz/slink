<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { type PickerColor, pickerEmptyIconTheme } from './picker.theme';

  interface Props {
    icon?: Snippet;
    message?: Snippet;
    description?: Snippet;
    color?: PickerColor;
  }

  let { icon, message, description, color = 'blue' }: Props = $props();
</script>

{#snippet defaultIcon()}
  <Icon icon="ph:list-dashes" class="h-[18px] w-[18px]" />
{/snippet}

{#snippet defaultMessage()}
  No items yet
{/snippet}

<div class="flex items-start gap-2.5 px-3 py-3">
  <span class={pickerEmptyIconTheme({ color })} aria-hidden="true">
    {@render (icon ?? defaultIcon)()}
  </span>
  <div class="min-w-0">
    <p class="text-[13px] font-medium text-gray-700 dark:text-gray-200">
      {@render (message ?? defaultMessage)()}
    </p>
    {#if description}
      <p class="mt-0.5 text-xs leading-snug text-gray-400 dark:text-gray-500">
        {@render description()}
      </p>
    {/if}
  </div>
</div>
