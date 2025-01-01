<script lang="ts">
  import { onMount, setContext } from 'svelte';
  import type { HTMLBaseAttributes } from 'svelte/elements';

  import { debounce } from '@slink/utils/time/debounce';
  import { className } from '@slink/utils/ui/className';

  import {
    type TabMenuContext,
    type TabMenuItemData,
    type TabMenuProps,
  } from '@slink/components/UI/Navigation';
  import { TabMenuTheme } from '@slink/components/UI/Navigation/TabMenu/TabMenu.theme';

  interface Props extends TabMenuProps, HTMLBaseAttributes {}

  let {
    variant = 'default',
    size = 'sm',
    orientation = 'horizontal',
    rounded = 'lg',
    children,
    ...props
  }: Props = $props();

  let items: Record<string, TabMenuItemData> = {};
  let activeItem: TabMenuItemData;
  let marker: HTMLElement;

  const handleItemRegister = (item: TabMenuItemData) => {
    items[item.key] = item;

    if (item.active) {
      activeItem = item;
    }
  };

  const handleItemSelect = (key: string) => {
    const item = items[key];

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

  function moveMarker(tab: TabMenuItemData, { withTransition = true } = {}) {
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

    if (marker.style.hasOwnProperty('transition')) {
      marker.style.transition = '';
    }

    if (!withTransition) {
      marker.style.transition = 'none';
    }
  }

  function handleMouseEnter(key: string) {
    const tab = items[key];

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

  const resizeHandler = debounce(() => {
    activeItem && moveMarker(activeItem, { withTransition: false });
  }, 10);

  $effect(() => {
    moveMarker(activeItem);
  });

  let isHorizontal: boolean = $derived(orientation === 'horizontal');

  let classes = $derived(
    className(
      TabMenuTheme({
        variant,
        size,
        orientation,
        rounded,
      }),
      props.class,
    ),
  );

  let menuRef: HTMLElement | null = $state(null);

  onMount(() => {
    if (!menuRef) {
      return;
    }

    const resizeObserver: ResizeObserver = new ResizeObserver(resizeHandler);
    resizeObserver.observe(menuRef);

    return () => {
      resizeObserver.disconnect();
    };
  });
</script>

<div bind:this={menuRef} class={classes} role="tablist" tabindex="0">
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
    ></div>
  </div>

  {@render children?.()}
</div>
