<script lang="ts">
  import { TabMenu, TabMenuItem } from '@slink/feature/Navigation';

  import Icon from '@iconify/svelte';

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
  <div class="px-6 pt-6 pb-4">
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
        Administration
      </h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage your application settings and users
      </p>
    </div>

    <TabMenu variant="default" size="sm" orientation="horizontal" class="w-fit">
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
