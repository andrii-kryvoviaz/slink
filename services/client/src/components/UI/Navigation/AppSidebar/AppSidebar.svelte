<script lang="ts">
  import type { AppSidebarGroup } from './AppSidebar.types';
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { settings } from '@slink/lib/settings';

  import { className } from '@slink/utils/ui/className';

  import {
    AppSidebarContent,
    AppSidebarFooter,
    AppSidebarHeader,
    AppSidebarTheme,
  } from './AppSidebar.theme';
  import SidebarGroup from './SidebarGroup.svelte';
  import SidebarUser from './SidebarUser.svelte';

  interface Props {
    user?: Partial<User>;
    groups?: AppSidebarGroup[];
    variant?: 'default' | 'minimal';
    className?: string;
    defaultExpanded?: boolean;
  }

  let {
    user = {},
    groups = [],
    variant = 'default',
    className: customClassName = '',
    defaultExpanded = true,
  }: Props = $props();

  let mobileOpen = $state(false);

  const sidebarSettings = settings.get('sidebar', {
    expanded: defaultExpanded,
  });
  const { expanded } = sidebarSettings;

  let collapsed = $derived(!$expanded);

  const isVisible = $derived(!!user && groups.length > 0);
  const sidebarWidth = $derived(collapsed ? 64 : 256);

  const toggleMobile = () => (mobileOpen = !mobileOpen);
  const closeMobile = () => (mobileOpen = false);

  const toggleCollapse = () => {
    settings.set('sidebar', { expanded: !$expanded });
  };

  const getThemeClasses = (isMobile = false) => ({
    header: AppSidebarHeader({ collapsed: isMobile ? false : collapsed }),
    content: AppSidebarContent(),
    footer: AppSidebarFooter(),
  });
</script>

{#if isVisible}
  <button
    class={className(
      'fixed top-2 z-50 md:hidden w-10 h-10 rounded-lg bg-background/80 backdrop-blur-sm hover:bg-accent/50 transition-all duration-300 flex items-center justify-center',
      mobileOpen ? 'right-4' : 'left-4',
    )}
    onclick={toggleMobile}
    aria-label={mobileOpen ? 'Close menu' : 'Open menu'}
  >
    <Icon icon={mobileOpen ? 'ph:x' : 'ph:list'} class="h-5 w-5" />
  </button>

  <button
    class="fixed top-3 z-50 hidden md:flex w-8 h-8 rounded-lg backdrop-blur-sm border border-bc-header cursor-pointer transition-colors items-center justify-center"
    style:left="{sidebarWidth + 16}px"
    onclick={toggleCollapse}
    aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
  >
    <Icon
      icon={collapsed ? 'ph:sidebar-simple' : 'ph:sidebar-simple-fill'}
      class="h-4 w-4"
    />
  </button>

  {#if mobileOpen}
    <div
      class="fixed inset-0 z-40 bg-black md:hidden"
      onclick={closeMobile}
      onkeydown={(e) => e.key === 'Escape' && closeMobile()}
      transition:fade={{ duration: 200 }}
      aria-label="Close sidebar"
      role="button"
      tabindex="-1"
    ></div>
  {/if}

  <aside
    class={className(
      AppSidebarTheme({ variant, size: collapsed ? 'collapsed' : 'expanded' }),
      customClassName,
      'hidden md:flex md:flex-col border-r h-screen overflow-y-auto',
      collapsed ? 'w-16 min-w-16' : 'w-64 min-w-64',
    )}
  >
    {@render SidebarContent()}
  </aside>

  <aside
    class={className(
      AppSidebarTheme({ variant, size: 'expanded' }),
      customClassName,
      'fixed inset-y-0 left-0 z-50 md:hidden transform transition-transform duration-300 w-64 bg-background border-none',
      mobileOpen ? 'translate-x-0' : '-translate-x-full',
    )}
  >
    {@render MobileSidebarContent()}
  </aside>
{/if}

{#snippet SidebarContent()}
  {@const { header, content, footer } = getThemeClasses()}

  <div class={header}>
    <div class="flex items-center gap-3">
      <div
        class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 hover:border-primary/20 hover:scale-105 transition-all duration-200 cursor-pointer flex items-center justify-center"
      >
        <img src="/favicon.png" alt="Slink" class="h-5 w-5" />
      </div>
      {#if !collapsed}
        <span class="font-semibold text-foreground tracking-tight">Slink</span>
      {/if}
    </div>
  </div>

  <div class={className(content, 'sidebar-scrollbar')}>
    {#each groups as group (group.id)}
      <SidebarGroup {group} {collapsed} onItemClick={closeMobile} />
    {/each}
  </div>

  {#if user}
    <div class={footer}>
      <SidebarUser {user} {collapsed} onItemClick={closeMobile} />
    </div>
  {/if}
{/snippet}

{#snippet MobileSidebarContent()}
  {@const { header, content, footer } = getThemeClasses(true)}

  <div class={header}>
    <div class="flex items-center gap-3">
      <div
        class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 hover:border-primary/20 hover:scale-105 transition-all duration-200 cursor-pointer flex items-center justify-center"
      >
        <img src="/favicon.png" alt="Slink" class="h-5 w-5" />
      </div>
      <span class="font-semibold text-foreground tracking-tight">Slink</span>
    </div>
  </div>

  <div class={className(content, 'sidebar-scrollbar')}>
    {#each groups as group (group.id)}
      <SidebarGroup {group} collapsed={false} onItemClick={closeMobile} />
    {/each}
  </div>

  {#if user}
    <div class={footer}>
      <SidebarUser {user} collapsed={false} onItemClick={closeMobile} />
    </div>
  {/if}
{/snippet}
