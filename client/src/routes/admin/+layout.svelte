<script lang="ts">
  import Icon from '@iconify/svelte';

  import { TabMenu, TabMenuItem } from '@slink/components/Layout';

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

<div class="flex h-full flex-col overflow-y-auto">
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

  <div class="flex max-w-full flex-grow">
    <slot />
  </div>
</div>
