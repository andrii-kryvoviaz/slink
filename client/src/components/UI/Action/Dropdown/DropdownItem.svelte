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
  <span
    class="flex w-full cursor-pointer items-center gap-3 rounded-md p-1 px-3 text-left text-sm text-input hover:bg-dropdown-accent hover:text-white disabled:cursor-not-allowed disabled:text-gray-500 disabled:hover:bg-inherit disabled:hover:text-gray-500"
    class:hover:bg-red-500={danger}
    class:hover:text-gray-50={danger}
  >
    {#if loading}
      <Icon icon="mingcute:loading-line" class="animate-spin" />
    {:else}
      {@render icon?.()}
    {/if}
    {@render children?.()}
  </span>
</DropdownMenu.Item>
