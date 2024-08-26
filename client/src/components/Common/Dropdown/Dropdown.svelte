<script lang="ts">
  import { createEventDispatcher, setContext } from 'svelte';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    Button,
    type ButtonAttributes,
    type DropdownContext,
    type DropdownItemData,
    type DropdownValue,
  } from '@slink/components/Common';
  import type { DropdownPosition } from '@slink/components/Common/Dropdown/Dropdown.types';

  interface $$Props extends ButtonAttributes {
    position?: DropdownPosition;
    selected?: DropdownValue;
    hideSelected?: boolean;
    closeOnSelect?: boolean;
    close?: () => void;
  }

  export let position: DropdownPosition = 'bottom-right';
  export let selected: DropdownValue | null = null;
  export let hideSelected: boolean = false;
  export let closeOnSelect: boolean = true;
  export const close = () => {
    isOpen = false;
  };

  const dispatch = createEventDispatcher<{ change: DropdownItemData }>();

  const defaultButtonProps: Partial<ButtonAttributes> = {
    variant: 'invisible',
    size: 'xs',
    rounded: 'full',
  };

  let container: HTMLElement;
  let menu: HTMLElement;
  let isOpen = false;

  const toggleDropdown = () => {
    isOpen = !isOpen;
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (container && !container.contains(event.target as Node)) {
      isOpen = false;
    }
  };

  let items: Record<string, DropdownItemData> = {};
  let selectedItem: DropdownItemData | null = null;

  const handleDropdownItemRegister = (item: DropdownItemData) => {
    items[item.key] = item;

    if (selected === item.key) {
      selectedItem = item;
      return;
    }

    if (!selectedItem) {
      selectedItem = item;
    }
  };

  const handleDropdownItemSelect = (item: DropdownItemData) => {
    if (closeOnSelect) {
      isOpen = false;
    }

    if (selectedItem === item) {
      return;
    }

    selectedItem = item;

    dispatch('change', item);
  };

  setContext<DropdownContext>('dropdown', {
    onRegister: handleDropdownItemRegister,
    onSelect: handleDropdownItemSelect,
  });

  const mobileContentClasses = 'fixed inset-0 m-auto';

  const baseContentClasses =
    'md:absolute md:inset-auto md:m-1 z-50 mx-auto w-56 origin-top-left max-h-fit divide-y divide-gray-100 rounded-md bg-zinc-800 font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-zinc-700 dark:bg-zinc-900';

  const classMap = {
    'bottom-right': 'md:top-full md:right-0',
    'bottom-left': 'md:top-full md:left-0',
    'top-right': 'md:bottom-full md:right-0',
    'top-left': 'md:bottom-full md:left-0',
  };

  $: contentClasses = `${mobileContentClasses} ${baseContentClasses} ${classMap[position]}`;

  $: buttonProps = {
    ...defaultButtonProps,
    ...$$props,
  };

  $: if (isOpen && menu) {
    menu.animate(
      [
        { opacity: 0, transform: 'translateY(-10px)' },
        { opacity: 1, transform: 'translateY(0)' },
      ],
      {
        duration: 200,
        easing: 'ease',
      }
    );
  }
</script>

<svelte:body on:click={handleClickOutside} />

<div class={`relative w-fit ${$$props.class}`} bind:this={container}>
  <div
    on:click={toggleDropdown}
    on:keydown={(event) => event.key === 'Enter' && toggleDropdown()}
    role="combobox"
    aria-controls="dropdown-menu"
    aria-expanded={isOpen}
    tabindex="0"
  >
    <Button {...buttonProps}>
      <div class="flex items-center justify-between gap-2">
        {#if !hideSelected}
          {#if selectedItem}
            <span>{selectedItem.name}</span>
          {:else}
            <span>Select</span>
          {/if}
        {/if}

        <div class:hidden={!isOpen}>
          <Icon icon="akar-icons:chevron-up" />
        </div>
        <div class:hidden={isOpen}>
          <Icon icon="akar-icons:chevron-down" />
        </div>
      </div>
    </Button>
  </div>

  <div
    role="menu"
    tabindex="-1"
    on:contextmenu|preventDefault
    class={className(contentClasses)}
    class:hidden={!isOpen}
    bind:this={menu}
  >
    <div class="flex flex-col gap-2 p-3">
      <slot />
    </div>
  </div>
  <button
    class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-md md:hidden"
    class:hidden={!isOpen}
    on:click={() => (isOpen = false)}
  />
</div>
