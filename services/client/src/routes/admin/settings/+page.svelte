<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  const categories = [
    {
      title: 'Image Settings',
      description:
        'Configure image uploads, processing options, and URL sharing preferences',
      href: '/admin/settings/image',
      icon: 'solar:gallery-linear',
      iconBg: 'bg-blue-100 dark:bg-blue-900/30',
      iconColor: 'text-blue-600 dark:text-blue-400',
    },
    {
      title: 'Storage Configuration',
      description:
        'Configure storage providers, cache settings, and data management',
      href: '/admin/settings/storage',
      icon: 'solar:database-linear',
      iconBg: 'bg-purple-100 dark:bg-purple-900/30',
      iconColor: 'text-purple-600 dark:text-purple-400',
    },
    {
      title: 'Security Settings',
      description:
        'Manage user registration, access control, and authentication policies',
      href: '/admin/settings/security',
      icon: 'solar:shield-check-linear',
      iconBg: 'bg-emerald-100 dark:bg-emerald-900/30',
      iconColor: 'text-emerald-600 dark:text-emerald-400',
    },
  ];
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

<div
  class="flex flex-col w-full max-w-4xl px-6 py-6"
  in:fade={{ duration: 300 }}
>
  <div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
      Settings
    </h1>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      Configure your application preferences and manage system behavior
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    {#each categories as category}
      <a
        href={category.href}
        class="group relative flex flex-col p-6 bg-white dark:bg-gray-900/50 border border-gray-200/60 dark:border-gray-700/40 rounded-2xl transition-all duration-300 hover:shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-gray-900/50 hover:border-gray-300 dark:hover:border-gray-700/60 hover:-translate-y-0.5"
      >
        <div class="flex items-start justify-between mb-4">
          <div
            class="flex items-center justify-center w-12 h-12 rounded-xl {category.iconBg} transition-transform duration-300 group-hover:scale-110"
          >
            <Icon icon={category.icon} class="w-6 h-6 {category.iconColor}" />
          </div>
          <Icon
            icon="ph:arrow-right"
            class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-all duration-300 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0"
          />
        </div>

        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          {category.title}
        </h3>

        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
          {category.description}
        </p>
      </a>
    {/each}
  </div>
</div>
