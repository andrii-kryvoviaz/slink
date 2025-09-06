<script lang="ts">
  import ChartLineIcon from '@lucide/svelte/icons/chart-line';
  import ClockIcon from '@lucide/svelte/icons/clock';
  import CompassIcon from '@lucide/svelte/icons/compass';
  import ExternalLinkIcon from '@lucide/svelte/icons/external-link';
  import GithubIcon from '@lucide/svelte/icons/github';
  import HelpCircleIcon from '@lucide/svelte/icons/help-circle';
  import PlusIcon from '@lucide/svelte/icons/plus';
  import Settings2Icon from '@lucide/svelte/icons/settings-2';
  import UsersIcon from '@lucide/svelte/icons/users';
  import type { AppSidebarGroup } from '@slink/feature/Navigation/AppSidebar/AppSidebar.types';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';

  const iconMap: Record<string, any> = {
    'ph:compass': CompassIcon,
    'ph:plus': PlusIcon,
    'ph:clock-counter-clockwise': ClockIcon,
    'ph:chart-line': ChartLineIcon,
    'ph:users': UsersIcon,
    'ph:gear-fine-light': Settings2Icon,
    'ph:question': HelpCircleIcon,
    'ph:github-logo': GithubIcon,
  };

  let {
    group,
    onNavigate,
  }: { group: AppSidebarGroup; onNavigate?: () => void } = $props();
</script>

<Sidebar.Group>
  {#if group.title}
    <Sidebar.GroupLabel>{group.title}</Sidebar.GroupLabel>
  {/if}
  <Sidebar.Menu>
    {#each group.items as item (item.id)}
      {@const isExternalLink = item.href?.startsWith('http')}
      <Sidebar.MenuItem>
        <Sidebar.MenuButton tooltipContent={item.title}>
          {#snippet child({ props })}
            <a
              href={item.href || '#'}
              {...props}
              target={isExternalLink ? '_blank' : undefined}
              rel={isExternalLink ? 'noopener noreferrer' : undefined}
              onclick={() => onNavigate?.()}
            >
              {#if item.icon && iconMap[item.icon]}
                {@const IconComponent = iconMap[item.icon]}
                <IconComponent class="w-4 h-4" />
              {/if}
              <span>{item.title}</span>
              {#if isExternalLink}
                <ExternalLinkIcon class="w-3 h-3 ml-auto opacity-60" />
              {/if}
            </a>
          {/snippet}
        </Sidebar.MenuButton>
      </Sidebar.MenuItem>
    {/each}
  </Sidebar.Menu>
</Sidebar.Group>
