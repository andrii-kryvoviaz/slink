<script lang="ts">
  import { DropdownMenu } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  type Props = DropdownMenu.ItemProps & {
    danger?: boolean;
    disabled?: boolean; // ToDo: handle disabled state
    loading?: boolean;
    icon?: Snippet;
    children?: Snippet;
    on?: {
      click: (event: Event) => void;
    };
  };

  let {
    danger = false,
    disabled = false,
    loading = false,
    icon,
    children,
    on,
    ...props
  }: Props = $props();

  function handleClick(event: Event) {
    if (disabled) {
      return;
    }

    on?.click(event);
  }
</script>

<DropdownMenu.Item {...props} onSelect={handleClick}>
  <span class="tooltip-item" class:danger>
    {#if loading}
      <Icon icon="mingcute:loading-line" class="animate-spin" />
    {:else}
      {@render icon?.()}
    {/if}
    {@render children?.()}
  </span>
</DropdownMenu.Item>

<style>
  .tooltip-item {
    @apply flex w-full cursor-pointer items-center gap-3 rounded-md p-1 px-3 text-left text-sm;
    @apply text-input-default;
  }

  .tooltip-item[disabled] {
    @apply cursor-not-allowed text-gray-500;
  }

  .tooltip-item:hover {
    @apply bg-dropdown-accent text-white;
  }

  .danger:hover {
    @apply bg-red-500 text-gray-50;
  }

  .tooltip-item[disabled]:hover {
    @apply bg-inherit text-gray-500;
  }
</style>
