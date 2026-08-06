<script lang="ts">
  import { Subtitle, Title } from '@slink/feature/Text';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  const categories = [
    {
      title: 'Image Settings',
      description:
        'Configure image uploads, processing options, and URL sharing preferences',
      href: '/admin/settings/image',
      icon: 'solar:gallery-linear',
      iconBg: 'bg-info-fill/15',
      iconColor: 'text-info',
    },
    {
      title: 'Storage Configuration',
      description:
        'Configure storage providers, cache settings, and data management',
      href: '/admin/settings/storage',
      icon: 'solar:database-linear',
      iconBg: 'bg-accent-strong/15',
      iconColor: 'text-accent-strong',
    },
    {
      title: 'Security Settings',
      description:
        'Manage user registration, access control, and authentication policies',
      href: '/admin/settings/security',
      icon: 'solar:shield-check-linear',
      iconBg: 'bg-success-strong/15',
      iconColor: 'text-success-strong',
    },
    {
      title: 'Single Sign-On',
      description:
        'Manage SSO/OIDC identity providers for external authentication',
      href: '/admin/settings/sso',
      icon: 'solar:key-linear',
      iconBg: 'bg-accent/15',
      iconColor: 'text-accent',
    },
    {
      title: 'Customization',
      description: 'Customize the site name, description, logo, and branding',
      href: '/admin/settings/customization',
      icon: 'solar:palette-linear',
      iconBg: 'bg-danger-strong/15',
      iconColor: 'text-danger-strong',
    },
  ];
</script>

<svelte:head>
  <title>Settings | Slink</title>
</svelte:head>

<div class="flex flex-col w-full px-6 py-6" in:fade={{ duration: 300 }}>
  <div class="mb-8">
    <Title>Settings</Title>
    <Subtitle
      >Configure your application preferences and manage system behavior</Subtitle
    >
  </div>

  <div class="grid grid-cols-[repeat(auto-fill,minmax(16rem,1fr))] gap-4">
    {#each categories as category}
      <a
        href={category.href}
        class="group relative flex flex-col p-6 bg-card/50 border border-border/60 rounded-2xl transition-all duration-300 hover:shadow-lg hover:shadow-border/50 dark:hover:shadow-card/50 hover:border-border-strong hover:-translate-y-0.5"
      >
        <div class="flex items-start justify-between mb-4">
          <div
            class="flex items-center justify-center w-12 h-12 rounded-xl {category.iconBg} transition-transform duration-300 group-hover:scale-110"
          >
            <Icon icon={category.icon} class="w-6 h-6 {category.iconColor}" />
          </div>
          <Icon
            icon="ph:arrow-right"
            class="w-5 h-5 text-ring transition-all duration-300 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0"
          />
        </div>

        <h3 class="text-lg font-medium text-foreground mb-2">
          {category.title}
        </h3>

        <p class="text-sm text-muted-foreground leading-relaxed">
          {category.description}
        </p>
      </a>
    {/each}
  </div>
</div>
