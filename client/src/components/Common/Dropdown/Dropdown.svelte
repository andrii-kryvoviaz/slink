<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import { Button } from '@slink/components/Common';
  import type { DropdownPosition } from '@slink/components/Common/Dropdown/Dropdown.types';

  export let position: DropdownPosition = 'bottom-right';

  let container: HTMLElement;
  let isOpen = false;

  const toggleDropdown = () => {
    isOpen = !isOpen;
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (container && !container.contains(event.target as Node)) {
      isOpen = false;
    }
  };

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
</script>

<svelte:body on:click={handleClickOutside} />

<div class="relative" bind:this={container}>
  <div on:click={toggleDropdown}>
    <slot name="button">
      <Button variant="invisible" size="xs" rounded="full">
        <div class:hidden={!isOpen}>
          <Icon icon="akar-icons:chevron-up" />
        </div>
        <div class:hidden={isOpen}>
          <Icon icon="akar-icons:chevron-down" />
        </div>
      </Button>
    </slot>
  </div>

  {#if isOpen}
    <div
      role="menu"
      tabindex="-1"
      on:contextmenu|preventDefault
      class={className(contentClasses)}
      in:fly={{ y: 10 }}
    >
      <slot />
    </div>
    <button
      class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-md md:hidden"
      on:click={() => (isOpen = false)}
    />
  {/if}
</div>
