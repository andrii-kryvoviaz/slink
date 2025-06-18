<script lang="ts">
  import type { AppSidebarGroup, AppSidebarItem } from './AppSidebar.types';

  import { page } from '$app/stores';

  import {
    AppSidebarGroup as AppSidebarGroupTheme,
    AppSidebarGroupTitle,
  } from './AppSidebar.theme';
  import SidebarItem from './SidebarItem.svelte';

  interface Props {
    group: AppSidebarGroup;
    collapsed: boolean;
    onItemClick: (item: AppSidebarItem) => void;
  }

  let { group, collapsed, onItemClick }: Props = $props();

  const isItemActive = (item: AppSidebarItem): boolean => {
    if (!item.href) return false;
    return (
      $page.route.id?.startsWith(item.href) || $page.url.pathname === item.href
    );
  };

  const getItemVariant = (item: AppSidebarItem) => {
    if (isItemActive(item)) return 'active';
    if (item.id === 'logout') return 'destructive';
    return 'default';
  };
</script>

<div class={AppSidebarGroupTheme()}>
  {#if group.title && !collapsed}
    <h3 class={AppSidebarGroupTitle({ collapsed })}>
      {group.title}
    </h3>
  {/if}

  <div class="space-y-1">
    {#each group.items as item (item.id)}
      <SidebarItem
        {item}
        variant={getItemVariant(item)}
        {collapsed}
        onClick={onItemClick}
      />
    {/each}
  </div>
</div>
