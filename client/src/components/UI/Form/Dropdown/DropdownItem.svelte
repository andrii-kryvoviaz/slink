<script lang="ts">
  import { createEventDispatcher, getContext, onMount } from 'svelte';

  import Icon from '@iconify/svelte';

  import { randomId } from '@slink/utils/string/randomId';

  import type {
    DropdownContext,
    DropdownItemData,
  } from '@slink/components/UI/Form';

  export let danger = false;
  export let disabled = false;
  export let loading = false;
  export let key: string = randomId('dropdown-item');

  let textItemRef: HTMLSpanElement;
  let itemData: DropdownItemData | null = null;

  const { onRegister, onSelect } = getContext<DropdownContext>('dropdown');
  const dispatch = createEventDispatcher<{ click: MouseEvent }>();

  function handleClick(event: MouseEvent) {
    if (disabled || !itemData) {
      return;
    }

    onSelect(itemData);
    dispatch('click', event);
  }

  onMount(() => {
    itemData = { key, name: textItemRef.innerText };

    onRegister(itemData);
  });
</script>

<button
  type="button"
  class="tooltip-item"
  class:danger
  on:click={handleClick}
  {disabled}
>
  {#if loading}
    <Icon icon="mingcute:loading-line" class="animate-spin" />
  {:else}
    <slot name="icon" />
  {/if}
  <span bind:this={textItemRef}>
    <slot />
  </span>
</button>

<style>
  .tooltip-item {
    @apply flex w-full cursor-pointer items-center gap-2 rounded-md p-1 px-3;
    @apply text-gray-50;
  }

  .tooltip-item[disabled] {
    @apply cursor-not-allowed text-gray-500;
  }

  .tooltip-item:hover {
    @apply bg-gray-500 text-gray-50;
  }

  .danger:hover {
    @apply bg-red-500 text-gray-50;
  }

  .tooltip-item[disabled]:hover {
    @apply bg-inherit text-gray-500;
  }
</style>
