<script lang="ts">
  import { type Snippet, setContext } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    type MultiselectContext,
    type MultiselectItemData,
    type MultiselectType,
    type MultiselectValue,
  } from '@slink/components/UI/Form';

  interface Props {
    type?: MultiselectType;
    placeholder?: string;
    value?: MultiselectValue<MultiselectType>;
    name?: string;
    children?: Snippet;
  }

  let {
    type = 'regular',
    placeholder = 'None selected',
    value = $bindable(),
    children,
    ...props
  }: Props = $props();

  let isOpen = $state(false);
  let container: HTMLElement;
  let menu: HTMLElement;

  let items: Record<string, MultiselectItemData> = $state({});

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
    value: MultiselectValue<MultiselectType> | undefined,
    items: Record<string, MultiselectItemData>,
  ) => {
    if (!value) {
      return [];
    }

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
    'relative w-fit max-w-[350px] cursor-pointer select-none rounded-md border border-input-default text-input-default';
  const focusContainerClasses =
    'border-blue-400 ring-blue-400 dark:bg-gray-800 ring ring-opacity-40 bg-gray-50 dark:border-gray-700 dark:text-gray-50';

  const mobileContentClasses = 'fixed inset-0 m-auto';
  const baseContentClasses =
    'md:absolute md:inset-auto md:m-1 min-w-full z-50 mx-auto w-56 origin-top-left max-h-fit divide-y divide-gray-100 rounded-md bg-white font-light shadow-lg ring-1 ring-black ring-opacity-5 dark:divide-zinc-700 dark:bg-zinc-900 select-none md:top-full md:right-0';

  let containerClasses = $derived(
    className(
      isOpen
        ? twMerge(baseContainerClasses, focusContainerClasses)
        : baseContainerClasses,
    ),
  );

  let contentClasses = $derived(
    className(`${mobileContentClasses} ${baseContentClasses}`),
  );

  let selectedItems: MultiselectItemData[] = $derived(
    handleUpdate(value, items),
  );
</script>

<svelte:body on:click={handleClickOutside} />

<div class={containerClasses} bind:this={container}>
  <input type="hidden" name={props.name} {value} />

  <div
    class="flex flex-wrap gap-2 rounded-md bg-input-default p-2"
    onclick={toggle}
    onkeydown={(event) => event.key === 'Enter' && toggle()}
    role="combobox"
    aria-controls="dropdown-menu"
    aria-expanded={isOpen}
    tabindex="0"
  >
    {#if selectedItems.length === 0}
      <div class="p-1.5 text-xs">{placeholder}</div>
    {/if}
    {#each selectedItems as item}
      <div
        class="flex flex-grow items-center justify-between gap-2 rounded-md bg-gray-200/70 p-1 text-xs dark:bg-gray-700 dark:text-gray-50"
      >
        {@html item.name}
        <div
          class="cursor-pointer rounded-md p-1 hover:bg-gray-300 dark:hover:bg-gray-600"
          onclick={(event) => {
            event.stopPropagation();
            handleRemove(item.key);
          }}
          onkeydown={(event) => event.key === 'Enter' && handleRemove(item.key)}
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
    type="button"
    class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-md md:hidden"
    class:hidden={!isOpen}
    onclick={() => (isOpen = false)}
  />
</div>
