<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';
  import { quintOut } from 'svelte/easing';
  import { fade } from 'svelte/transition';

  import {
    UserAvatar,
    UserDropdownItem,
    UserDropdownItems,
  } from '@slink/components/Feature/User';
  import { TextEllipsis } from '@slink/components/UI/Text';

  export let user: Partial<User> = {};
  export let isDark = false;

  let isOpen = false;

  const handleOutsideClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.dropdown-caller')) {
      isOpen = false;
    }
  };

  const handleToggle = () => {
    isOpen = !isOpen;
  };

  const isAuthorized = (roles: string[]) => {
    if (user) {
      return roles.some((role) => user.roles.includes(role));
    }

    return false;
  };

  $: icon = isOpen ? 'entypo:chevron-small-up' : 'entypo:chevron-small-down';
  $: mobileIcon = isOpen
    ? 'material-symbols-light:close'
    : 'material-symbols-light:menu';
</script>

<svelte:body on:click={handleOutsideClick} />

<div class="dropdown inline-block sm:relative">
  <button
    class="dropdown-caller z-100 relative flex items-center gap-2 rounded-md border border-transparent bg-gray-200/70 p-2 text-sm focus:border-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 dark:bg-slate-800 dark:text-white dark:focus:ring-blue-400 dark:focus:ring-opacity-40"
    on:click={handleToggle}
  >
    <UserAvatar
      size="xs"
      variant="default"
      class="mx-1 hidden sm:block"
      {user}
    />
    <span class="hidden sm:block">
      <TextEllipsis
        class="flex justify-center gap-1"
        fadeClass="from-gray-200/70 dark:from-slate-800"
      >
        {user?.displayName}
      </TextEllipsis>
    </span>

    <Icon {icon} class="hidden h-5 w-5 sm:block" />
    <Icon icon={mobileIcon} class="block h-5 w-5 sm:hidden" />
  </button>

  {#if isOpen}
    <div
      transition:fade={{ duration: 400, easing: quintOut }}
      class="fixed right-0 top-20 z-50 h-[calc(100vh-80px)] w-full origin-top-right overflow-hidden bg-gray-50 from-bg-start to-bg-end py-0 shadow-xl dark:bg-gradient-to-b sm:absolute sm:top-auto sm:mt-2 sm:h-auto sm:w-56 sm:rounded-md sm:dark:from-gray-800 sm:dark:to-gray-800"
    >
      {#each UserDropdownItems as group, index (group.title)}
        {#if isAuthorized(group.access)}
          {#if index > 0}
            <hr
              class="border-bottom mx-6 my-1 border-gray-200 opacity-70 dark:border-gray-700 sm:mx-0"
            />
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
              {#if item.state !== 'hidden' && isAuthorized(item.access)}
                <UserDropdownItem {item} {isDark}>
                  <span class="flex items-center gap-1">
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
        {/if}
      {/each}
    </div>
  {/if}
</div>
