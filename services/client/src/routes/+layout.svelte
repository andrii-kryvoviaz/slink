<script lang="ts">
  import { DemoNotice, ThemeSwitch } from '@slink/feature/Layout';
  import { AppFooter } from '@slink/feature/Layout/Footer';
  import { Navbar } from '@slink/feature/Navigation';
  import AppSidebar from '@slink/feature/Navigation/Sidebar/AppSidebar.svelte';
  import { Version } from '@slink/feature/Settings/Version';
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';
  import { Separator } from '@slink/ui/components/separator/index.js';
  import * as Sidebar from '@slink/ui/components/sidebar/index.js';
  import { Toaster } from '@slink/ui/components/sonner/index.js';

  import '@slink/app.css';

  import { theme } from '@slink/lib/actions/theme';
  import { isAdmin } from '@slink/lib/auth/utils';
  import { settings } from '@slink/lib/settings';
  import { initResponsiveStore } from '@slink/lib/stores/responsive.svelte';

  let { data, children } = $props();
  let user = $derived(data.user);
  let sidebarGroups = $derived(data.sidebarGroups || []);
  let isDemoMode = $derived(!!data.globalSettings?.demo?.enabled);
  let userIsAdmin = $derived(isAdmin(user));

  const currentTheme = settings.get('theme', data.settings.theme);
  const { isDark } = currentTheme;

  let showSidebar = $derived(!!user);

  const sidebarSettings = settings.get('sidebar', data.settings.sidebar || {});
  const { expanded } = sidebarSettings;

  let sidebarOpen = $state($expanded ?? true);

  $effect(() => {
    if ($expanded !== sidebarOpen) {
      settings.set('sidebar', { expanded: sidebarOpen });
    }
  });

  initResponsiveStore();
</script>

<div class="relative flex h-screen" use:theme={$currentTheme}>
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
        >
          {#snippet themeSwitch()}
            <ThemeSwitch
              checked={$isDark}
              variant="default"
              animation="none"
              on={{ change: (theme) => settings.set('theme', theme) }}
            />
          {/snippet}
        </Navbar>
      </header>
      {#if isDemoMode}
        <DemoNotice visible={true} />
      {/if}
      <div class="flex flex-1 flex-col min-h-0 relative">
        <ScrollArea
          class="flex-1 h-full"
          type="scroll"
          orientation="vertical"
          scrollbarYClasses="w-2"
        >
          {@render children?.()}
        </ScrollArea>

        <AppFooter />

        {#if !showSidebar}
          <div class="absolute bottom-16 right-4 z-10">
            <Version
              showUpdateIndicator={true}
              className="opacity-60 hover:opacity-100 transition-opacity"
            />
          </div>
        {/if}
      </div>
    </Sidebar.Inset>
  </Sidebar.Provider>

  <Toaster />
</div>
