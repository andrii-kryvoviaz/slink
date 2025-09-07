<script lang="ts">
  import ChevronsUpDownIcon from '@lucide/svelte/icons/chevrons-up-down';
  import LinkIcon from '@lucide/svelte/icons/link';
  import LogOutIcon from '@lucide/svelte/icons/log-out';
  import UserIcon from '@lucide/svelte/icons/user';
  import { UserAvatar } from '@slink/feature/User';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';
  import { useSidebar } from '@slink/ui/components/sidebar/index.js';

  import { enhance } from '$app/forms';

  let {
    user,
    onNavigate,
  }: {
    user: { displayName: string; email: string; avatar: string };
    onNavigate?: () => void;
  } = $props();
  const sidebar = useSidebar();
</script>

<Sidebar.Menu>
  <Sidebar.MenuItem>
    <DropdownMenu.Root>
      <DropdownMenu.Trigger>
        {#snippet child({ props })}
          <Sidebar.MenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground hover:bg-sidebar-accent/50 transition-colors duration-200"
            tooltipContent={user.displayName}
            {...props}
          >
            <UserAvatar {user} size="md" />
            <div
              class="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden"
            >
              <span class="truncate font-semibold">{user.displayName}</span>
              <span class="truncate text-xs text-muted-foreground"
                >{user.email}</span
              >
            </div>
            <ChevronsUpDownIcon
              class="ml-auto size-4 text-muted-foreground group-hover:text-sidebar-accent-foreground transition-colors duration-200 group-data-[collapsible=icon]:hidden"
            />
          </Sidebar.MenuButton>
        {/snippet}
      </DropdownMenu.Trigger>
      <DropdownMenu.Content
        class="w-(--bits-dropdown-menu-anchor-width) min-w-64 rounded-xl border shadow-lg"
        side={sidebar.isMobile ? 'bottom' : 'right'}
        align="end"
        sideOffset={8}
      >
        <DropdownMenu.Label class="p-0 font-normal">
          <div class="flex items-center gap-3 px-3 py-3 text-left">
            <UserAvatar {user} size="lg" />
            <div class="grid flex-1 text-left leading-tight">
              <span class="truncate font-semibold text-sm"
                >{user.displayName}</span
              >
              <span class="truncate text-xs text-muted-foreground"
                >{user.email}</span
              >
            </div>
          </div>
        </DropdownMenu.Label>
        <DropdownMenu.Separator />
        <DropdownMenu.Group>
          <DropdownMenu.Item>
            {#snippet child({ props })}
              <a
                href="/profile"
                {...props}
                class="flex items-center gap-3 w-full px-2 py-1.5 text-sm font-medium rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-900 dark:hover:text-blue-200 focus:bg-blue-50 dark:focus:bg-blue-900/20 focus:text-blue-900 dark:focus:text-blue-200 transition-all duration-200 group"
                onclick={() => onNavigate?.()}
              >
                <UserIcon
                  class="size-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-900 dark:group-hover:text-blue-200 transition-colors duration-200"
                />
                <span>Account Settings</span>
              </a>
            {/snippet}
          </DropdownMenu.Item>
          <DropdownMenu.Item>
            {#snippet child({ props })}
              <a
                href="/integrations"
                {...props}
                class="flex items-center gap-3 w-full px-2 py-1.5 text-sm font-medium rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-900 dark:hover:text-blue-200 focus:bg-blue-50 dark:focus:bg-blue-900/20 focus:text-blue-900 dark:focus:text-blue-200 transition-all duration-200 group"
                onclick={() => onNavigate?.()}
              >
                <LinkIcon
                  class="size-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-900 dark:group-hover:text-blue-200 transition-colors duration-200"
                />
                <span>Integrations</span>
              </a>
            {/snippet}
          </DropdownMenu.Item>
        </DropdownMenu.Group>
        <DropdownMenu.Separator />
        <DropdownMenu.Item>
          {#snippet child({ props })}
            <form
              action="/profile/logout"
              method="POST"
              use:enhance
              class="w-full"
            >
              <button
                type="submit"
                {...props}
                class="flex cursor-pointer items-center gap-3 w-full px-2 py-1.5 text-sm font-medium text-left rounded-md hover:bg-destructive/10 hover:text-destructive focus:bg-destructive/10 focus:text-destructive transition-all duration-200"
                onclick={() => onNavigate?.()}
              >
                <LogOutIcon class="size-4" />
                <span>Sign Out</span>
              </button>
            </form>
          {/snippet}
        </DropdownMenu.Item>
      </DropdownMenu.Content>
    </DropdownMenu.Root>
  </Sidebar.MenuItem>
</Sidebar.Menu>
