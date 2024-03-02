<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';
  import { quintOut } from 'svelte/easing';
  import { fade } from 'svelte/transition';

  import { TextEllipsis } from '@slink/components/Common';
  import { UserAvatar } from '@slink/components/User';
  import { UserDropdownItems } from '@slink/components/User/UserDropdown/UserDropdown.items';
  import UserDropdownItem from '@slink/components/User/UserDropdown/UserDropdownItem.svelte';

  export let user: Partial<User> = {};
  export let isDark = false;

  let isOpen = false;

  const handleOutsideClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.dropdown-caller')) {
      isOpen = false;
    }
  };

  const isAuthorized = (roles: string[]) => {
    if (user) {
      return roles.some((role) => user?.roles?.includes(role));
    }
    return false;
  };
</script>

<svelte:body on:click={handleOutsideClick} />

<div class="dropdown relative inline-block">
  <button
    class="dropdown-caller z-100 relative flex items-center gap-2 rounded-md border border-transparent bg-gray-200/70 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 dark:bg-slate-800 dark:text-white dark:focus:ring-blue-400 dark:focus:ring-opacity-40"
    on:click={() => (isOpen = !isOpen)}
  >
    <UserAvatar size="xs" variant="default" class="mx-1" {user} />
    <TextEllipsis
      class="justify-center"
      fadeClass="from-gray-200/70 dark:from-slate-800"
    >
      {user?.displayName}
    </TextEllipsis>
    {#if isOpen}
      <Icon icon="entypo:chevron-small-up" class="h-5 w-5" />
    {:else}
      <Icon icon="entypo:chevron-small-down" class="h-5 w-5" />
    {/if}
  </button>

  {#if isOpen}
    <div
      transition:fade={{ duration: 400, easing: quintOut }}
      class="absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-md border bg-gray-50 py-0 shadow-xl dark:border-header/70 dark:bg-gray-800"
    >
      {#each UserDropdownItems as group, index (group.title)}
        {#if index > 0}
          <hr class="border-gray-200 dark:border-gray-700" />
        {/if}

        <div class="relative">
          {#if group.badge}
            <span
              class="absolute -top-2 right-2 z-10 mx-1 rounded-full bg-primary px-2 py-1 text-[0.5rem] text-white"
            >
              {group.badge}
            </span>
          {/if}

          {#each group.items as item (item.title)}
            {#if item.state !== 'hidden'}
              <UserDropdownItem {item} {isDark}>
                <span class="flex gap-1">
                  <Icon icon={item.icon} class="mx-1 h-5 w-5" />

                  <span>{item.title}</span>
                </span>
                {#if item.badge}
                  <span
                    class="rounded-full bg-primary px-2 py-[0.1rem] text-[0.5rem] text-white"
                  >
                    {item.badge}
                  </span>
                {/if}
              </UserDropdownItem>
            {/if}
          {/each}
        </div>
      {/each}
    </div>
  {/if}
</div>
