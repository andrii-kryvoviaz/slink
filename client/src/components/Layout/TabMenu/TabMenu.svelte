<script lang="ts">
  import { setContext } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    type TabMenuContext,
    type TabMenuItemData,
    type TabMenuProps,
  } from '@slink/components/Layout';
  import { TabMenuTheme } from '@slink/components/Layout/TabMenu/TabMenu.theme';

  interface $$Props extends TabMenuProps {}

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'sm';
  export let orientation: $$Props['orientation'] = 'horizontal';
  export let rounded: $$Props['rounded'] = 'lg';

  let items: Record<string, TabMenuItemData> = {};
  let activeItem: TabMenuItemData;
  let marker: HTMLElement;

  const handleItemRegister = (item: TabMenuItemData) => {
    items[item.key] = item;

    if (item.active) {
      activeItem = item;
    }
  };

  const handleItemSelect = (item: TabMenuItemData) => {
    if (activeItem === item) {
      return;
    }

    if (activeItem) {
      activeItem.active = false;
    }

    activeItem = item;
  };

  setContext<TabMenuContext>('tab-menu', {
    onRegister: handleItemRegister,
    onSelect: handleItemSelect,
    onMouseEnter: handleMouseEnter,
    onMouseLeave: handleMouseLeave,
  });

  function moveMarker(tab: TabMenuItemData) {
    if (!marker || !tab.ref) {
      return;
    }

    if (orientation === 'vertical') {
      marker.style.transform = `translateY(${tab.ref.offsetTop}px)`;
      marker.style.height = `${tab.ref.offsetHeight}px`;
      return;
    }

    marker.style.transform = `translateX(${tab.ref.offsetLeft}px)`;
    marker.style.width = `${tab.ref.offsetWidth}px`;
  }

  function handleMouseEnter(tab: TabMenuItemData) {
    if (tab.active) {
      return;
    }

    moveMarker(tab);
  }

  function handleMouseLeave() {
    if (!activeItem) {
      return;
    }

    moveMarker(activeItem);
  }

  $: activeItem && moveMarker(activeItem);
  $: isHorizontal = orientation === 'horizontal';

  $: classes = `${TabMenuTheme({
    variant,
    size,
    orientation,
    rounded,
  })} ${$$props.class}`;
</script>

<div class={className(classes)} role="tablist" tabindex="0">
  <div
    class="absolute"
    class:inset-y-1={isHorizontal}
    class:left-0={isHorizontal}
    class:inset-x-1={!isHorizontal}
    class:top-0={!isHorizontal}
  >
    <div
      bind:this={marker}
      class="absolute h-full w-full rounded-lg bg-white transition-all duration-300 dark:bg-black"
    />
  </div>

  <slot />
</div>
