<script lang="ts">
  import { DropdownMenu } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { Loader } from '@slink/components/UI/Loader';

  type Props = DropdownMenu.ItemProps & {
    danger?: boolean;
    disabled?: boolean;
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
    class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-700 dark:text-gray-200 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
    class:hover:bg-red-50={danger}
    class:hover:text-red-600={danger}
    class:opacity-70={loading}
    class:pointer-events-none={loading}
    class:dark:hover:bg-red-900={danger}
    class:dark:hover:text-red-400={danger}
  >
    <div class="flex-shrink-0 w-4 h-4 flex items-center justify-center">
      {#if loading}
        <Loader variant="minimal" size="xs" />
      {:else}
        {@render icon?.()}
      {/if}
    </div>
    <span class="flex-1 min-w-0 truncate">
      {@render children?.()}
    </span>
  </span>
</DropdownMenu.Item>
