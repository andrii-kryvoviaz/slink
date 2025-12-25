<script lang="ts">
  import * as Collapsible from '@slink/ui/components/collapsible';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu';
  import { useSidebar } from '@slink/ui/components/sidebar/context.svelte.js';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';

  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';

  import { settings } from '@slink/lib/settings';

  import type { AppSidebarGroup, AppSidebarItem } from './types';

  type Props = Record<string, unknown>;

  let {
    group,
    onNavigate,
  }: { group: AppSidebarGroup; onNavigate?: () => void } = $props();

  const sidebar = useSidebar();

  function isActiveRoute(href: string) {
    return $page.url.pathname === href;
  }

  function isChildActive(item: AppSidebarItem): boolean {
    if (!item.children) return false;
    return item.children.some(
      (child) => child.href && isActiveRoute(child.href),
    );
  }

  const savedGroups = $page.data.settings?.navigation?.expandedGroups || {};

  function buildInitialExpandedState(): Record<string, boolean> {
    const items = group.items;
    return Object.fromEntries(
      items
        .filter((item) => item.children?.length)
        .map((item) => [item.id, savedGroups[item.id] ?? isChildActive(item)]),
    );
  }

  let expandedItems: Record<string, boolean> = $state(
    buildInitialExpandedState(),
  );

  settings.get('navigation', { expandedGroups: expandedItems });

  function setExpandedState(itemId: string, value: boolean) {
    expandedItems[itemId] = value;
    settings.set('navigation', { expandedGroups: { ...expandedItems } });
  }

  function toggleExpanded(itemId: string) {
    setExpandedState(itemId, !expandedItems[itemId]);
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
      {@const hasChildren = item.children && item.children.length > 0}

      {#if hasChildren}
        {#if sidebar.state === 'collapsed' && !sidebar.isMobile}
          <DropdownMenu.Root>
            <DropdownMenu.Trigger>
              {#snippet child({ props })}
                <Sidebar.MenuItem>
                  <Sidebar.MenuButton
                    {...props}
                    isActive={isActive || isChildActive(item)}
                    class="hover:pl-4 data-[active=true]:pl-4 group-data-[collapsible=icon]:hover:pl-2"
                  >
                    {#snippet child({ props: buttonProps }: { props: Props })}
                      <span {...buttonProps}>
                        {#if item.icon}
                          <Icon icon={item.icon} class="w-4 h-4" />
                        {/if}
                        <span class="group-data-[collapsible=icon]:hidden"
                          >{item.title}</span
                        >
                      </span>
                    {/snippet}
                  </Sidebar.MenuButton>
                </Sidebar.MenuItem>
              {/snippet}
            </DropdownMenu.Trigger>
            <DropdownMenu.Content side="right" align="start" class="min-w-48">
              <DropdownMenu.Item
                class="font-medium"
                onclick={() => onNavigate?.()}
              >
                {#snippet child({ props })}
                  <a
                    href={item.href || '#'}
                    {...props}
                    class="{props.class} gap-2"
                  >
                    {#if item.icon}
                      <Icon icon={item.icon} class="w-4 h-4" />
                    {/if}
                    {item.title}
                  </a>
                {/snippet}
              </DropdownMenu.Item>
              <DropdownMenu.Separator />
              {#each item.children as subItem (subItem.id)}
                {@const isSubItemActive = Boolean(
                  subItem.href && isActiveRoute(subItem.href),
                )}
                <DropdownMenu.Item onclick={() => onNavigate?.()}>
                  {#snippet child({ props })}
                    <a
                      href={subItem.href || '#'}
                      {...props}
                      class="{props.class} gap-2"
                      data-active={isSubItemActive}
                    >
                      {#if subItem.icon}
                        <Icon icon={subItem.icon} class="w-4 h-4" />
                      {/if}
                      <span class:font-medium={isSubItemActive}
                        >{subItem.title}</span
                      >
                    </a>
                  {/snippet}
                </DropdownMenu.Item>
              {/each}
            </DropdownMenu.Content>
          </DropdownMenu.Root>
        {:else}
          <Collapsible.Root
            open={expandedItems[item.id] ?? false}
            onOpenChange={(value) => setExpandedState(item.id, value)}
            class="group/collapsible"
          >
            <Sidebar.MenuItem>
              <div
                class="group/item flex w-full items-center rounded-md overflow-hidden"
                data-active={isActive || isChildActive(item)}
              >
                <Sidebar.MenuButton
                  isActive={isActive || isChildActive(item)}
                  class="flex-1 rounded-r-none hover:pl-4 data-[active=true]:pl-4 group-hover/item:bg-indigo-50/80 dark:group-hover/item:bg-indigo-950/30 group-hover/item:text-indigo-700 dark:group-hover/item:text-indigo-300 transition-all duration-200 ease-out"
                >
                  {#snippet child({ props }: { props: Props })}
                    <a
                      href={item.href || '#'}
                      {...props}
                      onclick={() => onNavigate?.()}
                    >
                      {#if item.icon}
                        <Icon icon={item.icon} class="w-4 h-4" />
                      {/if}
                      <span>{item.title}</span>
                    </a>
                  {/snippet}
                </Sidebar.MenuButton>
                <Collapsible.Trigger>
                  {#snippet child({ props })}
                    <button
                      {...props}
                      onclick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleExpanded(item.id);
                      }}
                      aria-label={expandedItems[item.id]
                        ? 'Collapse submenu'
                        : 'Expand submenu'}
                      class="flex h-8 w-8 shrink-0 items-center justify-center rounded-r-md text-muted-foreground group-hover/item:bg-indigo-50/80 dark:group-hover/item:bg-indigo-950/30 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400 group-data-[active=true]/item:bg-indigo-50/80 dark:group-data-[active=true]/item:bg-indigo-950/30 group-data-[active=true]/item:text-indigo-700 dark:group-data-[active=true]/item:text-indigo-300 hover:text-indigo-800 dark:hover:text-indigo-200 transition-all duration-200 ease-out cursor-pointer"
                    >
                      <Icon
                        icon="ph:caret-right"
                        class="w-4 h-4 transition-transform duration-200 ease-out hover:scale-110 {(expandedItems[
                          item.id
                        ] ?? false)
                          ? 'rotate-90'
                          : ''}"
                      />
                    </button>
                  {/snippet}
                </Collapsible.Trigger>
              </div>
              <Collapsible.Content>
                <Sidebar.MenuSub>
                  {#each item.children as subItem (subItem.id)}
                    {@const isSubItemActive = Boolean(
                      subItem.href && isActiveRoute(subItem.href),
                    )}
                    <Sidebar.MenuSubItem>
                      <Sidebar.MenuSubButton isActive={isSubItemActive}>
                        {#snippet child({ props }: { props: Props })}
                          <a
                            href={subItem.href || '#'}
                            {...props}
                            onclick={() => onNavigate?.()}
                          >
                            {#if subItem.icon}
                              <Icon icon={subItem.icon} class="w-3.5 h-3.5" />
                            {/if}
                            <span>{subItem.title}</span>
                          </a>
                        {/snippet}
                      </Sidebar.MenuSubButton>
                    </Sidebar.MenuSubItem>
                  {/each}
                </Sidebar.MenuSub>
              </Collapsible.Content>
            </Sidebar.MenuItem>
          </Collapsible.Root>
        {/if}
      {:else}
        <Sidebar.MenuItem>
          <Sidebar.MenuButton
            tooltipContent={item.title}
            {isActive}
            class="hover:pl-4 data-[active=true]:pl-4 group-data-[collapsible=icon]:hover:pl-2"
          >
            {#snippet child({ props }: { props: Props })}
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
      {/if}
    {/each}
  </Sidebar.Menu>
</Sidebar.Group>
