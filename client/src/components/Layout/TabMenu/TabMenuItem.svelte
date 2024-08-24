<script lang="ts">
  import { createEventDispatcher, getContext, onMount } from 'svelte';

  import { page } from '$app/stores';

  import { randomId } from '@slink/utils/string/randomId';
  import { className } from '@slink/utils/ui/className';

  import {
    type TabMenuContext,
    type TabMenuItemData,
  } from '@slink/components/Layout';

  export let key: string = randomId('tab-menu-item');
  export let href: string | undefined;

  let active: boolean = false;

  let ref: HTMLElement;
  let itemData: TabMenuItemData;

  const defaultClasses =
    'relative z-10 flex cursor-pointer items-center gap-2 px-3 py-1.5 text-center text-sm text-gray-400 transition-colors duration-300 dark:text-gray-400 dark:hover:text-white hover:text-black';
  const activeClasses = 'text-black dark:text-white';

  const { onRegister, onSelect, onMouseEnter, onMouseLeave } =
    getContext<TabMenuContext>('tab-menu');

  const dispatch = createEventDispatcher<{
    click: MouseEvent | KeyboardEvent;
  }>();

  const handleClick = (event: MouseEvent | KeyboardEvent) => {
    handleSelect();
    dispatch('click', event);
  };

  const handleSelect = () => {
    if (active) {
      return;
    }

    active = true;

    onSelect(itemData);
  };

  const initData = () => {
    itemData = {
      key,
      href,
      ref,
      get active() {
        return active;
      },
      set active(value) {
        active = value;
      },
    };
  };

  onMount(() => {
    initData();
    onRegister(itemData);
  });

  $: if (href === $page.route.id) {
    handleSelect();
  }

  $: classes = className(defaultClasses, { [activeClasses]: active });
</script>

{#if href}
  <a
    bind:this={ref}
    {href}
    class={classes}
    on:click={handleClick}
    on:mouseenter={() => onMouseEnter(itemData)}
    on:mouseleave={() => onMouseLeave(itemData)}
    tabindex="0"
    role="tab"
  >
    <slot />
  </a>
{:else}
  <div
    bind:this={ref}
    class={classes}
    on:click={handleClick}
    on:mouseenter={() => onMouseEnter(itemData)}
    on:mouseleave={() => onMouseLeave(itemData)}
    on:keydown={(event) => event.key === 'Enter' && handleClick(event)}
    tabindex="0"
    role="tab"
  >
    <slot />
  </div>
{/if}
