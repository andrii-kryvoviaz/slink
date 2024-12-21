<script lang="ts">
  import { type Snippet, getContext, onMount } from 'svelte';
  import type { HTMLAttributes } from 'svelte/elements';

  import { page } from '$app/stores';

  import { randomId } from '@slink/utils/string/randomId';
  import { className } from '@slink/utils/ui/className';

  import { type TabMenuContext } from '@slink/components/UI/Navigation';

  interface Props {
    key?: string;
    href?: string;
    children?: Snippet;
    on?: {
      click: (event: MouseEvent | KeyboardEvent) => void;
    };
  }

  let { key = randomId('tab-menu-item'), href, on, children }: Props = $props();

  let active: boolean = $state(href === $page.route.id);
  let ref: HTMLElement | undefined = $state();

  const defaultClasses =
    'relative z-10 flex cursor-pointer items-center gap-2 px-3 py-1.5 text-center text-sm text-gray-400 transition-colors duration-300 dark:text-gray-400 dark:hover:text-white hover:text-black';
  const activeClasses = 'text-black dark:text-white';

  let classes = $derived(
    className(defaultClasses, { [activeClasses]: active }),
  );

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
