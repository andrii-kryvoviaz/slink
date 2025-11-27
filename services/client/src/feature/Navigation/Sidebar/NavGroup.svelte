<script lang="ts">
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';

  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';

  import type { AppSidebarGroup } from './types';

  let {
    group,
    onNavigate,
  }: { group: AppSidebarGroup; onNavigate?: () => void } = $props();

  function isActiveRoute(href: string) {
    return $page.url.pathname === href;
  }
</script>

<Sidebar.Group>
  {#if group.title}
    <Sidebar.GroupLabel>{group.title}</Sidebar.GroupLabel>
  {/if}
  <Sidebar.Menu>
    {#each group.items as item (item.id)}
      {@const isExternalLink = item.href?.startsWith('http')}
      {@const isActive = Boolean(item.href && isActiveRoute(item.href))}
      <Sidebar.MenuItem>
        <Sidebar.MenuButton
          tooltipContent={item.title}
          {isActive}
          class="hover:pl-4 data-[active=true]:pl-4 group-data-[collapsible=icon]:hover:pl-2"
        >
          {#snippet child({ props }: { props: Record })}
            <a
              href={item.href || '#'}
              {...props}
              target={isExternalLink ? '_blank' : undefined}
              rel={isExternalLink ? 'noopener noreferrer' : undefined}
              onclick={() => onNavigate?.()}
            >
              {#if item.icon}
                <Icon icon={item.icon} class="w-4 h-4" />
              {/if}
              <span class="group-data-[collapsible=icon]:hidden"
                >{item.title}</span
              >
              {#if isExternalLink}
                <Icon
                  icon="ph:arrow-square-out"
                  class="w-3 h-3 ml-auto opacity-60 group-data-[collapsible=icon]:hidden"
                />
              {/if}
            </a>
          {/snippet}
        </Sidebar.MenuButton>
      </Sidebar.MenuItem>
    {/each}
  </Sidebar.Menu>
</Sidebar.Group>
