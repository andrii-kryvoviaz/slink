<script lang="ts">
  import type {
    DropdownContext,
    DropdownItemData,
    DropdownPosition,
    DropdownValue,
  } from '@slink/components/UI/Form';
  import { setContext } from 'svelte';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import { Button, type ButtonAttributes } from '@slink/components/UI/Action';

  interface Props extends ButtonAttributes {
    name?: string;
    position?: DropdownPosition;
    selected?: DropdownValue;
    hideSelected?: boolean;
    closeOnSelect?: boolean;
    on?: {
      change: (item: DropdownItemData) => void;
    };
  }

  export const close = () => {
    isOpen = false;
  };

  let {
    position = 'bottom-right',
    selected = $bindable(),
    hideSelected = false,
    closeOnSelect = true,
    children,
    ...props
  }: Props = $props();

  const defaultButtonProps: Partial<ButtonAttributes> = {
    variant: 'invisible',
    size: 'xs',
    rounded: 'full',
  };

  let container: HTMLElement;
  let menu: HTMLElement;
  let isOpen = $state(false);

  const toggleDropdown = () => {
    isOpen = !isOpen;
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (container && !container.contains(event.target as Node)) {
      isOpen = false;
    }
  };

  let items: Record<string, DropdownItemData> = $state({});

  let selectedItem: DropdownItemData | null = $derived(
    items[selected as string] || null,
  );

  const handleDropdownItemRegister = (item: DropdownItemData) => {
    items[item.key] = item;

    if (selected === item.key) {
      return;
    }

    if (!selectedItem && !selected) {
      selected = item.key;
    }
  };

  const handleDropdownItemSelect = (item: DropdownItemData) => {
    if (closeOnSelect) {
      isOpen = false;
    }

    if (selectedItem === item) {
      return;
    }

    selected = item.key;

    props.on?.change(item);
  };

  setContext<DropdownContext>('dropdown', {
    onRegister: handleDropdownItemRegister,
    onSelect: handleDropdownItemSelect,
  });

  const mobileContentClasses = 'fixed inset-0 m-auto';

  const baseContentClasses =
    'md:absolute md:inset-auto md:m-1 z-50 mx-auto w-fit min-w-56 origin-top-left max-h-fit divide-y divide-gray-100 rounded-md bg-white font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-zinc-700 dark:bg-zinc-900';

  const classMap = {
    'bottom-right': 'md:top-full md:right-0',
    'bottom-left': 'md:top-full md:left-0',
    'top-right': 'md:bottom-full md:right-0',
    'top-left': 'md:bottom-full md:left-0',
  };

  let contentClasses = $derived(
    className(
      `${mobileContentClasses} ${baseContentClasses} ${classMap[position]}`,
    ),
  );

  let buttonProps = $derived({
    ...defaultButtonProps,
    ...props,
  });

  $effect(() => {
    if (isOpen && menu) {
      menu.animate(
        [
          { opacity: 0, transform: 'translateY(-10px)' },
          { opacity: 1, transform: 'translateY(0)' },
        ],
        {
          duration: 200,
          easing: 'ease',
        },
      );
    }
  });
</script>

<svelte:body on:click={handleClickOutside} />

<div class={`relative w-fit ${props.class}`} bind:this={container}>
  <input type="hidden" name={props.name} value={selectedItem?.key} />

  <div
    onclick={toggleDropdown}
    onkeydown={(event) => event.key === 'Enter' && toggleDropdown()}
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
    oncontextmenu={(event) => event.preventDefault()}
    class={contentClasses}
    class:hidden={!isOpen}
    bind:this={menu}
  >
    <div class="flex flex-col gap-2 p-3">
      {@render children?.()}
    </div>
  </div>
  <button
    class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-md md:hidden"
    class:hidden={!isOpen}
    onclick={() => (isOpen = false)}
  />
</div>
