<script lang="ts">
  import { setContext } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    type MultiselectContext,
    type MultiselectItemData,
    type MultiselectType,
    type MultiselectValue,
  } from '@slink/components/Form';

  export let type: MultiselectType = 'regular';
  export let placeholder: string = 'None selected';
  export let value: MultiselectValue<typeof type> = [];

  let isOpen = false;
  let container: HTMLElement;
  let menu: HTMLElement;

  let items: Record<string, MultiselectItemData> = {};
  let selectedItems: MultiselectItemData[] = [];

  const toggle = () => {
    isOpen = !isOpen;
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (container && !container.contains(event.target as Node)) {
      isOpen = false;
    }
  };

  const handleMultiselectItemRegister = (item: MultiselectItemData) => {
    items[item.key] = item;
  };

  const handleMultiselectItemSelect = (item: MultiselectItemData) => {
    if (type === 'bitmask') {
      value = (value as number) ^ parseInt(item.key);
    } else {
      value = [...(value as string[]), item.key];
    }

    isOpen = false;
  };

  const handleUpdate = (
    value: number | string[],
    items: Record<string, MultiselectItemData>
  ) => {
    const itemValues = Object.values(items);

    if (type === 'bitmask' && typeof value === 'number') {
      return itemValues.filter((item) => {
        return value & parseInt(item.key);
      });
    }

    if (typeof value === 'number') {
      return itemValues.filter((item) => item.key === value.toString());
    }

    return itemValues.filter((item) => value.includes(item.key));
  };

  const handleRemove = (key: string) => {
    if (type === 'bitmask') {
      value = (value as number) ^ parseInt(key);
    } else {
      value = (value as string[]).filter((item) => item !== key);
    }
  };

  setContext<MultiselectContext>('dropdown', {
    onRegister: handleMultiselectItemRegister,
    onSelect: handleMultiselectItemSelect,
  });

  const baseContainerClasses =
    'relative w-fit max-w-[350px] cursor-pointer select-none rounded-md border border-gray-700';
  const focusContainerClasses =
    'border-blue-400 ring-blue-400 bg-gray-800 ring ring-opacity-40';

  const mobileContentClasses = 'fixed inset-0 m-auto';
  const baseContentClasses =
    'md:absolute md:inset-auto md:m-1 min-w-full z-50 mx-auto w-56 origin-top-left max-h-fit divide-y divide-gray-100 rounded-md bg-zinc-800 font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-zinc-700 dark:bg-zinc-900 select-none md:top-full md:right-0';

  $: containerClasses = isOpen
    ? twMerge(baseContainerClasses, focusContainerClasses)
    : baseContainerClasses;

  $: contentClasses = `${mobileContentClasses} ${baseContentClasses}`;
  $: selectedItems = handleUpdate(value, items);
</script>

<svelte:body on:click={handleClickOutside} />

<div class={className(containerClasses)} bind:this={container}>
  <div
    class="flex flex-wrap gap-2 rounded-md bg-gray-800 p-2"
    on:click={toggle}
    on:keydown={(event) => event.key === 'Enter' && toggle()}
    role="combobox"
    aria-controls="dropdown-menu"
    aria-expanded={isOpen}
    tabindex="0"
  >
    {#if selectedItems.length === 0}
      <div class="p-1.5 text-xs text-gray-50">{placeholder}</div>
    {/if}
    {#each selectedItems as item}
      <div
        class="flex flex-grow items-center justify-between gap-2 rounded-md bg-gray-700 p-1 text-xs text-gray-50"
      >
        {@html item.name}
        <div
          class="cursor-pointer rounded-md p-1 hover:bg-gray-600"
          on:click={(event) => {
            event.stopPropagation();
            handleRemove(item.key);
          }}
          on:keydown={(event) =>
            event.key === 'Enter' && handleRemove(item.key)}
          role="button"
          tabindex="0"
        >
          <Icon icon="ph:x" />
        </div>
      </div>
    {/each}
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
