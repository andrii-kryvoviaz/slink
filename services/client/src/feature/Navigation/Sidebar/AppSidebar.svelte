<script lang="ts">
  import type { SidebarConfig } from '@slink/feature/Navigation';
  import { NavGroup, NavUser } from '@slink/feature/Navigation';
  import type { AppSidebarGroup } from '@slink/feature/Navigation/AppSidebar/AppSidebar.types';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';
  import type { ComponentProps } from 'svelte';

  interface ExtendedSidebarConfig extends SidebarConfig {
    user?: any;
    groups?: AppSidebarGroup[];
  }

  let {
    ref = $bindable(null),
    collapsible = 'icon',
    config = {},
    ...restProps
  }: ComponentProps<typeof Sidebar.Root> & {
    config?: ExtendedSidebarConfig;
  } = $props();

  const createUser = (userConfig?: any) => ({
    displayName: userConfig?.displayName || userConfig?.name || 'User',
    email: userConfig?.email || '',
    avatar: userConfig?.avatar,
  });

  const user = $derived(createUser(config.user));
  const groups = $derived(config.groups || []);
</script>

<Sidebar.Root {collapsible} {...restProps}>
  <Sidebar.Header>
    <div class="flex items-center gap-2 px-2 py-3 relative">
      <div
        class="absolute inset-0 bg-gradient-to-r from-transparent via-sidebar-accent/5 to-transparent rounded-lg"
      ></div>
      <a
        href="/"
        class="relative w-8 h-8 rounded-lg bg-gradient-to-br from-sidebar-primary/15 via-sidebar-accent/20 to-sidebar-primary/10 border border-sidebar-border/40 flex items-center justify-center hover:border-sidebar-border/60 transition-all duration-300 hover:scale-105 backdrop-blur-sm group"
      >
        <img
          src="/favicon.png"
          alt="Slink"
          class="h-5 w-5 drop-shadow-sm transition-transform duration-300 group-hover:scale-110"
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
      <NavGroup {group} />
    {/each}
  </Sidebar.Content>
  {#if config.user}
    <Sidebar.Footer>
      <NavUser {user} />
    </Sidebar.Footer>
  {/if}
  <Sidebar.Rail />
</Sidebar.Root>
