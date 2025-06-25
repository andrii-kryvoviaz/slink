<script lang="ts">
  import type { AppSidebarItem } from './AppSidebar.types';

  import Icon from '@iconify/svelte';

  import { Tooltip } from '@slink/components/UI/Tooltip';

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
    isLink: !!item.href,
  });

  const elementProps = $derived({
    class: `${itemClasses} relative overflow-hidden`,
    onclick: handleClick,
    disabled: item.disabled,
    ...(content.isLink && {
      href: item.href,
      target: content.isExternal ? '_blank' : undefined,
      rel: content.isExternal ? 'noopener noreferrer' : undefined,
    }),
  });
</script>

{#if collapsed}
  <Tooltip side="right" variant="default" size="sm">
    {#snippet trigger()}
      <svelte:element this={content.isLink ? 'a' : 'button'} {...elementProps}>
        {@render itemContent()}
      </svelte:element>
    {/snippet}
    {content.title}
  </Tooltip>
{:else}
  <svelte:element this={content.isLink ? 'a' : 'button'} {...elementProps}>
    {@render itemContent()}
  </svelte:element>
{/if}

{#snippet itemContent()}
  <Icon icon={content.icon} class={AppSidebarIcon({ variant })} />

  <span
    class="{AppSidebarText({
      collapsed,
    })} absolute left-10 right-8 top-1/2 -translate-y-1/2"
  >
    {content.title}
  </span>

  <span
    class="{AppSidebarBadge({
      variant: content.badge?.variant || 'primary',
      collapsed,
    })} absolute right-2 top-1/2 -translate-y-1/2"
  >
    {content.badge?.text || ''}
  </span>

  {#if content.isExternal}
    <div
      class="absolute right-2 top-1/2 -translate-y-1/2 transition-all duration-300 {collapsed
        ? 'opacity-0 pointer-events-none scale-0'
        : 'opacity-60 scale-100'}"
    >
      <Icon
        icon="lucide:external-link"
        class="h-3 w-3 transition-opacity duration-300 shrink-0"
      />
    </div>
  {/if}
{/snippet}
