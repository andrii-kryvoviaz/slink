<script lang="ts">
  import { type Snippet, getContext, onMount } from 'svelte';
  import type { HTMLAttributes } from 'svelte/elements';

  import { page } from '$app/state';

  import { randomId } from '@slink/utils/string/randomId';
  import { className } from '@slink/utils/ui/className';

  import { type TabMenuContext } from '@slink/components/UI/Navigation';
  import { TabMenuItemTheme } from '@slink/components/UI/Navigation/TabMenu/TabMenu.theme';

  interface Props {
    key?: string;
    href?: string;
    variant?: 'default' | 'minimal' | 'pills' | 'underline';
    children?: Snippet;
    on?: {
      click: (event: MouseEvent | KeyboardEvent) => void;
    };
  }

  let {
    key = randomId('tab-menu-item'),
    href,
    variant = 'default',
    on,
    children,
  }: Props = $props();

  let active: boolean = $state(false);
  let ref: HTMLElement | undefined = $state();

  let classes = $derived(TabMenuItemTheme({ variant, active }));

  const { onRegister, onSelect, onMouseEnter, onMouseLeave } =
    getContext<TabMenuContext>('tab-menu');

  const handleClick = (event: MouseEvent | KeyboardEvent) => {
    active = true;
    on?.click(event);
  };

  onMount(() => {
    const item = createItem();
    if (!item) return;

    onRegister(item);
  });

  const createItem = () => {
    if (!ref) return;

    return {
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

  $effect(() => {
    if (active) {
      onSelect(key);
    }
  });

  $effect(() => {
    if (href) {
      const currentPath = page.url.pathname;
      const isExactMatch = currentPath === href;
      const isRouteMatch = page.route.id === href;
      active = isExactMatch || isRouteMatch;
    }
  });

  let defaultProps: Partial<HTMLAttributes<HTMLElement>> = $derived({
    class: classes,
    onclick: handleClick,
    onmouseenter: () => onMouseEnter(key),
    onmouseleave: () => onMouseLeave(key),
    tabindex: 0,
    role: 'tab',
  });
</script>

{#if href}
  <a bind:this={ref} {href} {...defaultProps}>
    {@render children?.()}
  </a>
{:else}
  <button
    bind:this={ref}
    {...defaultProps}
    onkeydown={(event) => event.key === 'Enter' && handleClick(event)}
  >
    {@render children?.()}
  </button>
{/if}
