<script lang="ts">
  import type { AppSidebarItem } from './AppSidebar.types';

  import Icon from '@iconify/svelte';

  import {
    AppSidebarBadge,
    AppSidebarIcon,
    AppSidebarItem as AppSidebarItemTheme,
    AppSidebarText,
  } from './AppSidebar.theme';

  interface Props {
    item: AppSidebarItem;
    variant: 'default' | 'active' | 'destructive';
    collapsed: boolean;
    onClick: (item: AppSidebarItem) => void;
  }

  let { item, variant, collapsed, onClick }: Props = $props();

  const handleClick = () => {
    onClick(item);
    if (item.action) {
      item.action();
    }
  };

  const itemClasses = $derived(
    AppSidebarItemTheme({
      variant,
      collapsed,
    }),
  );

  const content = $derived({
    icon: item.icon,
    title: item.title,
    badge: item.badge,
    isExternal: item.href?.startsWith('http'),
    showText: !collapsed,
    showBadge: !collapsed && item.badge,
  });
</script>

{#if item.href}
  <a
    href={item.href}
    class={itemClasses}
    onclick={handleClick}
    target={content.isExternal ? '_blank' : undefined}
    rel={content.isExternal ? 'noopener noreferrer' : undefined}
  >
    <Icon icon={content.icon} class={AppSidebarIcon({ variant })} />
    {#if !collapsed}
      <span class={AppSidebarText({ collapsed })}>{content.title}</span>
      {#if content.showBadge}
        <span
          class={AppSidebarBadge({
            variant: content.badge?.variant || 'primary',
            collapsed,
          })}
        >
          {content.badge?.text}
        </span>
      {/if}
      {#if content.isExternal}
        <Icon
          icon="lucide:external-link"
          class="h-3 w-3 ml-auto opacity-60 transition-opacity duration-300 shrink-0"
        />
      {/if}
    {/if}
  </a>
{:else}
  <button class={itemClasses} onclick={handleClick} disabled={item.disabled}>
    <Icon icon={content.icon} class={AppSidebarIcon({ variant })} />
    {#if !collapsed}
      <span class={AppSidebarText({ collapsed })}>{content.title}</span>
      {#if content.showBadge}
        <span
          class={AppSidebarBadge({
            variant: content.badge?.variant || 'primary',
            collapsed,
          })}
        >
          {content.badge?.text}
        </span>
      {/if}
    {/if}
  </button>
{/if}
