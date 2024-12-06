<script lang="ts">
  import type {
    DropdownContext,
    DropdownItemData,
  } from '@slink/components/UI/Form';
  import { type Snippet, getContext, onMount } from 'svelte';

  import Icon from '@iconify/svelte';

  import { randomId } from '@slink/utils/string/randomId';

  interface Props {
    danger?: boolean;
    disabled?: boolean;
    loading?: boolean;
    key?: string;
    icon?: Snippet;
    children?: Snippet;
    on?: {
      click: (event: MouseEvent) => void;
    };
  }

  let {
    danger = false,
    disabled = false,
    loading = false,
    key = randomId('dropdown-item'),
    icon,
    children,
    on,
  }: Props = $props();

  let textItemRef: HTMLSpanElement | undefined = $state();
  let itemData: DropdownItemData | null = null;

  const { onRegister, onSelect } = getContext<DropdownContext>('dropdown');

  function handleClick(event: MouseEvent) {
    if (disabled || !itemData) {
      return;
    }

    onSelect(itemData);
    on?.click(event);
  }

  onMount(() => {
    itemData = { key, name: textItemRef?.innerText ?? '' };

    onRegister(itemData);
  });
</script>

<button
  type="button"
  class="tooltip-item group"
  class:danger
  onclick={handleClick}
  {disabled}
>
  {#if loading}
    <Icon icon="mingcute:loading-line" class="animate-spin" />
  {:else}
    {@render icon?.()}
  {/if}
  <span bind:this={textItemRef}>
    {@render children?.()}
  </span>
</button>

<style>
  .tooltip-item {
    @apply flex w-full cursor-pointer items-center gap-3 rounded-md p-1 px-3 text-left;
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
