<script lang="ts">
  import { DemoNotice, ThemeSwitch } from '@slink/feature/Layout';
  import { AppFooter } from '@slink/feature/Layout/Footer';
  import { Navbar } from '@slink/feature/Navigation';
  import AppSidebar from '@slink/feature/Navigation/Sidebar/AppSidebar.svelte';
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';
  import { Separator } from '@slink/ui/components/separator/index.js';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';
  import { Toaster } from '@slink/ui/components/sonner/index.js';

  import '@slink/app.css';

  import { afterNavigate } from '$app/navigation';
  import { page } from '$app/state';

  import { theme } from '@slink/lib/actions/theme';
  import { isAdmin } from '@slink/lib/auth/utils';
  import { initResponsiveStore } from '@slink/lib/stores/responsive.svelte';

  const { settings } = page.data;

  let scrollAreaRef = $state<HTMLElement | null>(null);

  afterNavigate(() => {
    const viewport = scrollAreaRef?.querySelector(
      '[data-slot="scroll-area-viewport"]',
    );
    if (viewport) {
      viewport.scrollTop = 0;
    }
  });

  let { data, children } = $props();
  let user = $derived(data.user);
  let sidebarGroups = $derived(data.sidebarGroups || []);
  let isDemoMode = $derived(!!data.globalSettings?.demo?.enabled);
  let userIsAdmin = $derived(isAdmin(user));

  let showSidebar = $derived(!!user);

  let sidebarOpen = $state(settings.sidebar.expanded);

  $effect(() => {
    settings.sidebar = { expanded: sidebarOpen };
  });

  const siteName = $derived(
    data.globalSettings?.customization?.siteName || 'Slink',
  );
  const siteDescription = $derived(
    data.globalSettings?.customization?.siteDescription ||
      'Fast and secure image sharing service',
  );
  const logoUrl = $derived(
    data.globalSettings?.customization?.logoUrl || '/favicon.png',
  );
  const customCss = $derived(
    data.globalSettings?.customization?.customCss || '',
  );

  initResponsiveStore();
</script>

<svelte:head>
  <title>{siteName}: Image Sharing Service</title>
  <meta name="description" content={siteDescription} />
  <meta name="apple-mobile-web-app-title" content={siteName} />
  {#if logoUrl !== '/favicon.png'}
    <link rel="icon" href={logoUrl} />
    <link rel="apple-touch-icon" href={logoUrl} />
  {/if}
  {#if customCss}
    {@html `<style>${customCss}</style>`}
  {/if}
</svelte:head>

<div class="relative flex h-screen" use:theme={settings.theme.current}>
  <Sidebar.Provider bind:open={sidebarOpen}>
    {#if showSidebar}
      <AppSidebar
        config={{
          user: user || undefined,
          groups: sidebarGroups,
          showAdmin: userIsAdmin,
          showSystemItems: true,
          showUploadItem:
            !!user || !!data.globalSettings?.access?.allowGuestUploads,
          customization: data.globalSettings?.customization,
        }}
      />
    {/if}
    <Sidebar.Inset class="bg-transparent">
      <header
        class="group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 flex h-16 shrink-0 items-center justify-between gap-2 transition-[width,height] ease-linear border-b border-bc-header relative"
      >
        {#if showSidebar}
          <div class="flex items-center px-4 pr-0 sm:px-4">
            <Sidebar.Trigger />
            <Separator
              orientation="vertical"
              class="mx-2 data-[orientation=vertical]:h-4"
            />
          </div>
        {/if}
        <Navbar
          user={user || undefined}
          showLogo={!showSidebar}
          showLoginButton={!user}
          showUploadButton={!!user ||
            !!data.globalSettings?.access?.allowGuestUploads}
          customization={data.globalSettings?.customization}
        >
          {#snippet themeSwitch()}
            <ThemeSwitch
              checked={settings.theme.isDark}
              variant="default"
              animation="none"
              on={{ change: (theme) => (settings.theme.current = theme) }}
            />
          {/snippet}
        </Navbar>
      </header>
      {#if isDemoMode}
        <DemoNotice visible={true} />
      {/if}
      <div class="flex flex-1 flex-col min-h-0 relative">
        <ScrollArea
          bind:ref={scrollAreaRef}
          class="flex-1 min-h-0"
          type="scroll"
          orientation="vertical"
        >
          {@render children?.()}
        </ScrollArea>

        <AppFooter />
      </div>
    </Sidebar.Inset>
  </Sidebar.Provider>

  <Toaster />
</div>
