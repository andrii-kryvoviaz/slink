<script lang="ts">
  import { createEventDispatcher, getContext, onMount } from 'svelte';

  import { randomId } from '@slink/utils/string/randomId';

  import {
    type MultiselectContext,
    type MultiselectItemData,
  } from '@slink/components/UI/Form';

  export let key: string = randomId('multiselect-item');
  export let disabled = false;

  let textItemRef: HTMLSpanElement;
  let itemData: MultiselectItemData | null = null;

  const { onRegister, onSelect } = getContext<MultiselectContext>('dropdown');

  const dispatch = createEventDispatcher<{ click: MouseEvent }>();

  function handleClick(event: MouseEvent) {
    if (disabled || !itemData) {
      return;
    }

    onSelect(itemData);
    dispatch('click', event);
  }

  onMount(() => {
    itemData = { key, name: textItemRef.outerHTML };

    onRegister(itemData);
  });
</script>

<button
  class="w-full cursor-pointer rounded-md p-2 hover:bg-gray-600"
  type="button"
  on:click={handleClick}
>
  <span class="flex items-center gap-2" bind:this={textItemRef}>
    <slot />
  </span>
</button>
