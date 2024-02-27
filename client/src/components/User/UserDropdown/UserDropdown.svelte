<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';
  import { quintOut } from 'svelte/easing';
  import { fade } from 'svelte/transition';

  import { UserAvatar } from '@slink/components/User';
  import { UserDropdownItems } from '@slink/components/User/UserDropdown/UserDropdown.items';

  export let user: User | null = null;
  export let isDark = false;

  let isOpen = false;

  const handleOutsideClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.dropdown-caller')) {
      isOpen = false;
    }
  };

  const isAuthorized = (roles: string[]) => {
    if (user) {
      return roles.some((role) => user?.roles.includes(role));
    }
    return false;
  };
</script>

<svelte:body on:click={handleOutsideClick} />

<div class="dropdown relative inline-block">
  <button
    class="dropdown-caller z-100 relative flex items-center rounded-md border border-transparent bg-white p-2 text-sm text-gray-600 focus:border-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-400 dark:focus:ring-opacity-40"
    on:click={() => (isOpen = !isOpen)}
  >
    <span class="mx-1">{user?.displayName}</span>
    <Icon icon="mdi:chevron-up-down" class="mx-1 h-5 w-5" />
  </button>

  {#if isOpen}
    <div
      transition:fade={{ duration: 400, easing: quintOut }}
      class="absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden
      rounded-md bg-white py-2 shadow-xl dark:bg-gray-800"
    >
      <div
        class="-mt-2 flex transform cursor-auto select-none items-center p-3 text-sm text-gray-600 transition-colors duration-300 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
      >
        <UserAvatar size="sm" variant="ring" class="mx-2" />
        <div class="mx-1">
          <h1 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            {user?.displayName}
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {user?.email}
          </p>
        </div>
      </div>

      {#each UserDropdownItems as group (group.title)}
        <div class="relative" class:dark={isDark}>
          <hr class=" border-gray-200 dark:border-gray-700" />
          {#if group.badge}
            <span
              class="absolute -top-2 right-2 z-10 mx-1 rounded-full bg-primary px-2 py-1 text-[0.5rem] text-white"
            >
              {group.badge}
            </span>
          {/if}

          {#each group.items as item (item.title)}
            {#if item.state !== 'hidden'}
              <a
                href={item.state === 'active' ? item.link : '#'}
                class="flex transform items-center justify-between p-3 text-sm capitalize text-gray-600 transition-colors duration-300 hover:bg-primary hover:text-white dark:text-gray-300"
                class:inactive={item.state === 'inactive'}
              >
                <span class="flex">
                  <Icon icon={item.icon} class="mx-1 h-5 w-5" />

                  <span class="mx-1">{item.title}</span>
                </span>
                {#if item.badge}
                  <span
                    class="mx-1 rounded-full bg-primary px-2 py-[0.1rem] text-[0.5rem] text-white"
                  >
                    {item.badge}
                  </span>
                {/if}
              </a>
            {/if}
          {/each}
        </div>
      {/each}
    </div>
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
