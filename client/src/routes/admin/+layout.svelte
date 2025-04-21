<script lang="ts">
  import Icon from '@iconify/svelte';

  import { TabMenu, TabMenuItem } from '@slink/components/UI/Navigation';

  interface Props {
    children?: import('svelte').Snippet;
  }

  let { children }: Props = $props();

  const tabs = [
    {
      label: 'Overview',
      href: '/admin/dashboard',
      icon: 'solar:graph-line-duotone',
    },
    { label: 'Users', href: '/admin/user', icon: 'ph:user' },
    {
      label: 'Settings',
      href: '/admin/settings',
      icon: 'mingcute:settings-7-line',
    },
  ];
</script>

<div class="flex h-full w-full flex-col">
  <div class="p-6">
    <TabMenu orientation="horizontal" class="md:inline-flex">
      {#each tabs as tab}
        <TabMenuItem href={tab.href}>
          <Icon icon={tab.icon} class="h-4 w-4" />
          <span>{tab.label}</span>
        </TabMenuItem>
      {/each}
    </TabMenu>
  </div>

  <div class="flex max-w-full grow">
    {@render children?.()}
  </div>
</div>
