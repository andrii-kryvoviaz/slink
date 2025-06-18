<script lang="ts">
  import type {
    AppSidebarGroup,
    AppSidebarItem,
    AppSidebarProps,
  } from './AppSidebar.types';
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { fade, fly } from 'svelte/transition';

  import { className } from '@slink/utils/ui/className';

  import { createAppSidebarItems } from './AppSidebar.config';
  import {
    AppSidebarCollapseButton,
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
    collapsed?: boolean;
    variant?: AppSidebarProps['variant'];
    className?: string;
    width?: number;
    onItemSelect?: (item: AppSidebarItem) => void;
    onCollapseToggle?: (collapsed: boolean) => void;
  }

  let {
    user = {},
    groups,
    collapsed = false,
    variant = 'default',
    className: customClassName = '',
    width = 256,
    onItemSelect,
    onCollapseToggle,
  }: Props = $props();

  let innerWidth = $state(0);
  let isMobile = $derived(innerWidth < 768);
  let mobileOverlayOpen = $state(false);

  let isCollapsedDesktop = $state(collapsed);
  let effectiveCollapsed = $derived(isMobile ? false : isCollapsedDesktop);
  let showOverlay = $derived(isMobile && mobileOverlayOpen);

  const isAuthorized = (requiredRoles: string[] = ['ROLE_USER']): boolean => {
    if (!user?.roles) return false;
    return requiredRoles.some((role) => user.roles?.includes(role));
  };

  const sidebarGroups = $derived(
    groups ||
      createAppSidebarItems({
        showAdmin: isAuthorized(['ROLE_ADMIN']),
        showSystemItems: true,
      }),
  );

  const visibleGroups = $derived(
    sidebarGroups
      .map((group) => ({
        ...group,
        items: group.items.filter(
          (item) => !item.hidden && (!item.roles || isAuthorized(item.roles)),
        ),
      }))
      .filter(
        (group) =>
          !group.hidden &&
          group.items.length > 0 &&
          (!group.roles || isAuthorized(group.roles)),
      ),
  );

  const handleItemClick = (item: AppSidebarItem) => {
    onItemSelect?.(item);

    if (isMobile) {
      mobileOverlayOpen = false;
    }
  };

  const handleCollapseToggle = () => {
    if (isMobile) {
      mobileOverlayOpen = !mobileOverlayOpen;
    } else {
      const newCollapsed = !isCollapsedDesktop;
      isCollapsedDesktop = newCollapsed;
      onCollapseToggle?.(newCollapsed);
    }
  };

  $effect(() => {
    isCollapsedDesktop = collapsed;
  });

  const handleOverlayClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
      mobileOverlayOpen = false;
    }
  };

  const triggerPosition = $derived(
    isMobile ? 16 : effectiveCollapsed ? 72 : width + 8,
  );

  const sidebarSize = $derived(effectiveCollapsed ? 'collapsed' : 'expanded');

  const sidebarClasses = $derived(
    className(
      AppSidebarTheme({ variant, size: sidebarSize }),
      customClassName,
      isMobile
        ? 'fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 ease-in-out'
        : '',
      isMobile && !showOverlay ? '-translate-x-full' : '',
      isMobile && showOverlay ? 'translate-x-0' : '',
    ),
  );

  const headerClasses = $derived(
    AppSidebarHeader({ collapsed: effectiveCollapsed }),
  );
  const contentClasses = $derived(AppSidebarContent());
  const footerClasses = $derived(AppSidebarFooter());
</script>

<svelte:window bind:innerWidth />

{#if isMobile && showOverlay}
  <div
    class="fixed inset-0 z-30 bg-black bg-opacity-50 transition-opacity duration-300"
    role="button"
    tabindex="-1"
    onclick={handleOverlayClick}
    onkeydown={(e) => e.key === 'Escape' && (mobileOverlayOpen = false)}
    transition:fade={{ duration: 300 }}
    aria-label="Close sidebar"
  ></div>
{/if}

<button
  class="fixed top-3 z-50 transition-all duration-300 ease-out {AppSidebarCollapseButton()}"
  style:left="{triggerPosition}px"
  onclick={handleCollapseToggle}
  aria-label={isMobile
    ? showOverlay
      ? 'Close menu'
      : 'Open menu'
    : effectiveCollapsed
      ? 'Expand sidebar'
      : 'Collapse sidebar'}
>
  <Icon
    icon={isMobile
      ? 'ph:list'
      : effectiveCollapsed
        ? 'ph:sidebar-simple'
        : 'ph:sidebar-simple-fill'}
    class="h-4 w-4 transition-all duration-200"
  />
</button>

<aside
  class={sidebarClasses}
  transition:fly={{ x: -100, duration: 400, easing: cubicOut }}
>
  <div class={headerClasses}>
    {#if !effectiveCollapsed}
      <div class="flex items-center gap-3 animate-fade-in">
        <div
          class="flex items-center justify-center w-8 h-8 rounded-xl bg-muted/20 border-0 hover:bg-muted/30 hover:scale-105 transition-all duration-200 cursor-pointer"
        >
          <img src="/favicon.png" alt="Slink" class="h-5 w-5" />
        </div>
        <span class="font-semibold text-foreground tracking-tight">Slink</span>
      </div>
    {:else}
      <div
        class="flex items-center justify-center w-10 h-10 rounded-2xl bg-muted/20 border-0 hover:bg-muted/30 hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer hover:shadow-sm"
      >
        <img src="/favicon.png" alt="Slink" class="h-5 w-5" />
      </div>
    {/if}
  </div>

  <div class={className(contentClasses, 'sidebar-scrollbar')}>
    {#each visibleGroups as group (group.id)}
      <SidebarGroup
        {group}
        collapsed={effectiveCollapsed}
        onItemClick={handleItemClick}
      />
    {/each}
  </div>

  {#if user}
    <div class={footerClasses}>
      <SidebarUser {user} collapsed={effectiveCollapsed} />
    </div>
  {/if}
</aside>
