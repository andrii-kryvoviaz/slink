<script lang="ts">
  import { enhance } from '$app/forms';

  import { type UserDropdownItem } from '@slink/components/User';

  export let item: UserDropdownItem;
  export let isDark = false;

  const classes =
    'flex transform items-center justify-between sm:p-3 py-3 px-5 text-lg sm:text-sm capitalize text-gray-600 hover:bg-dropdown-accent hover:text-white dark:text-gray-300';
</script>

<div class:dark={isDark}>
  {#if item.isForm}
    <form action={item.link} method="POST" use:enhance>
      <button
        type="submit"
        class={classes + ' w-full'}
        class:inactive={item.state === 'inactive'}
      >
        <slot />
      </button>
    </form>
  {:else}
    <a
      href={item.state === 'active' ? item.link : '#'}
      target={item.target || '_self'}
      class={classes}
      class:inactive={item.state === 'inactive'}
    >
      <slot />
    </a>
  {/if}
</div>

<style>
  .dark .inactive {
    @apply cursor-not-allowed bg-gray-600 text-gray-300 opacity-60;
  }

  .dark .inactive:hover {
    @apply bg-gray-600;
  }

  .inactive {
    @apply bg-gray-100 text-gray-400;
  }

  .inactive:hover {
    @apply bg-gray-100;
  }
</style>
