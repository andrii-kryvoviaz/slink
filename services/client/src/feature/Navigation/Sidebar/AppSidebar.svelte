<script lang="ts">
  import type { SidebarConfig } from '@slink/feature/Navigation';
  import { NavGroup, NavUser } from '@slink/feature/Navigation';
  import type { AppSidebarGroup } from '@slink/feature/Navigation/Sidebar/types';
  import SidebarStorageUsage from './SidebarStorageUsage.svelte';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';
  import type { ComponentProps } from 'svelte';

  
import type { User } from '@slink/lib/auth/Type/User';

  interface ExtendedSidebarConfig extends SidebarConfig {
    user?: Partial<User>;
    groups?: AppSidebarGroup[];
  }

  interface Props extends ComponentProps<typeof Sidebar.Root> {
    config?: ExtendedSidebarConfig;
  }

  let {
    ref = $bindable(null),
    collapsible = 'icon',
    config = {},
    ...restProps
  }: Props = $props();

  const createUser = (userConfig?: any) => ({
    displayName: userConfig?.displayName || userConfig?.name || 'User',
    email: userConfig?.email || '',
    avatar: userConfig?.avatar,
  });

  const user = $derived(createUser(config.user));
  const groups = $derived(config.groups || []);

  const sidebar = Sidebar.useSidebar();

  function handleNavigate() {
    if (sidebar.isMobile) {
      sidebar.setOpenMobile(false);
    }
  }
</script>

<Sidebar.Root {collapsible} {...restProps}>
  <Sidebar.Header>
    <div
      class="flex items-center gap-2 px-2 py-3 relative group-data-[collapsible=icon]:px-0 group-data-[collapsible=icon]:py-1"
    >
      <div
        class="absolute inset-0 bg-gradient-to-r from-transparent via-sidebar-accent/5 to-transparent rounded-lg"
      ></div>
      <a
        href="/"
        class="relative w-8 h-8 rounded-lg bg-gradient-to-br from-sidebar-accent/30 via-sidebar-accent/40 to-sidebar-accent/20 border border-sidebar-border/60 flex items-center justify-center hover:border-sidebar-border/80 transition-all duration-300 hover:scale-105 group dark:from-sidebar-primary/15 dark:via-sidebar-accent/20 dark:to-sidebar-primary/10 dark:border-sidebar-border/40 dark:hover:border-sidebar-border/60"
      >
        <img
          src="/favicon.png"
          alt="Slink"
          class="size-5 object-contain drop-shadow-sm transition-transform duration-300 group-hover:scale-110"
        />
      </a>
      <a
        href="/"
        class="relative font-semibold text-sidebar-foreground/90 tracking-tight group-data-[collapsible=icon]:hidden hover:text-sidebar-foreground transition-colors"
        >Slink</a
      >
    </div>
  </Sidebar.Header>
  <Sidebar.Content>
    {#each groups as group (group.id)}
      <NavGroup {group} onNavigate={handleNavigate} />
    {/each}
  </Sidebar.Content>

  {#if config.showAdmin}
    <div
      class="px-2 pb-2 group-data-[collapsible=icon]:px-1 group-data-[collapsible=icon]:pb-1"
    >
      <SidebarStorageUsage />
    </div>
  {/if}

  {#if config.user}
    <Sidebar.Footer>
      <NavUser {user} onNavigate={handleNavigate} />
    </Sidebar.Footer>
  {/if}
  <Sidebar.Rail />
</Sidebar.Root>
