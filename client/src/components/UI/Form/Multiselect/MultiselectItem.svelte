<script lang="ts">
  import { type Snippet, getContext, onMount } from 'svelte';

  import { randomId } from '@slink/utils/string/randomId';

  import {
    type MultiselectContext,
    type MultiselectItemData,
  } from '@slink/components/UI/Form';

  interface Props {
    key?: string;
    disabled?: boolean;
    children?: Snippet;
    on?: {
      click: (event: MouseEvent) => void;
    };
  }

  let {
    key = randomId('multiselect-item'),
    disabled = false,
    children,
    on,
  }: Props = $props();

  let textItemRef: HTMLSpanElement | undefined = $state();
  let itemData: MultiselectItemData | null = null;

  const { onRegister, onSelect } = getContext<MultiselectContext>('dropdown');

  function handleClick(event: MouseEvent) {
    if (disabled || !itemData) {
      return;
    }

    onSelect(itemData);
    on?.click?.(event);
  }

  onMount(() => {
    itemData = { key, name: textItemRef?.outerHTML ?? '' };

    onRegister(itemData);
  });
</script>

<button
  class="w-full cursor-pointer rounded-md p-2 hover:bg-dropdown-accent hover:text-white"
  type="button"
  onclick={handleClick}
>
  <span class="flex items-center gap-2" bind:this={textItemRef}>
    {@render children?.()}
  </span>
</button>
