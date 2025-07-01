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
  let activeItem: TabMenuItemData | null = $state(null);
  let marker: HTMLElement;

  const handleItemRegister = (item: TabMenuItemData) => {
    items[item.key] = item;

    if (item.active) {
      activeItem = item;
    }
  };

  const handleItemSelect = (key: string) => {
    const item = items[key];

    if (activeItem?.key === item.key) {
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
    if (!marker || !tab.ref || !menuRef) {
      return;
    }

    requestAnimationFrame(() => {
      if (!marker || !tab.ref || !menuRef) return;

      const containerRect = menuRef.getBoundingClientRect();
      const tabRect = tab.ref.getBoundingClientRect();

      const containerStyles = getComputedStyle(menuRef);
      const paddingLeft = parseFloat(containerStyles.paddingLeft) || 0;
      const paddingTop = parseFloat(containerStyles.paddingTop) || 0;

      const relativeLeft = tabRect.left - containerRect.left - paddingLeft;
      const relativeTop = tabRect.top - containerRect.top - paddingTop;

      if (orientation === 'vertical') {
        marker.style.transform = `translateY(${relativeTop}px)`;
        marker.style.width = `${tabRect.width}px`;
        marker.style.height = `${tabRect.height}px`;
      } else {
        marker.style.transform = `translate(${relativeLeft}px, ${relativeTop}px)`;
        marker.style.width = `${tabRect.width}px`;
        marker.style.height = `${tabRect.height}px`;
      }

      if (!withTransition) {
        marker.style.transition = 'none';
        marker.offsetHeight;
      } else {
        marker.style.transition = 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
      }
    });
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
    if (activeItem) {
      requestAnimationFrame(() => {
        if (activeItem) {
          moveMarker(activeItem, { withTransition: false });
        }
      });
    }
  }, 10);

  $effect(() => {
    if (!activeItem) {
      return;
    }

    requestAnimationFrame(() => {
      if (activeItem) {
        moveMarker(activeItem);
      }
    });
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
  <div class="absolute inset-1 pointer-events-none overflow-hidden">
    <div
      bind:this={marker}
      class="absolute rounded-lg bg-white dark:bg-gray-700 shadow-sm shadow-black/10 dark:shadow-black/25 border border-gray-200/50 dark:border-gray-600/50 transition-all duration-200 ease-out"
    ></div>
  </div>

  {@render children?.()}
</div>
