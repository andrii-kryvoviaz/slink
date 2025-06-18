<script lang="ts">
  import { Tooltip } from 'bits-ui';

  import '@slink/app.css';

  import { theme } from '@slink/lib/actions/theme';
  import { settings } from '@slink/lib/settings';

  import { ThemeSwitch } from '@slink/components/UI/Action';
  import { AppSidebar, Navbar } from '@slink/components/UI/Navigation';
  import { ToastManager } from '@slink/components/UI/Toast';

  let { data, children } = $props();
  let user = $derived(data.user);

  const currentTheme = settings.get('theme', data.settings.theme);
  const { isDark } = currentTheme;

  let sidebarCollapsed = $state(false);
  let showSidebar = $derived(!!user);

  let innerWidth = $state(0);
  let isMobile = $derived(innerWidth < 768);
  let sidebarWidth = $derived(
    showSidebar ? (isMobile ? 0 : sidebarCollapsed ? 64 : 256) : 0,
  );

  const handleSidebarItemSelect = (item: any) => {};

  const handleSidebarCollapseToggle = (collapsed: boolean) => {
    sidebarCollapsed = collapsed;
  };
</script>

<svelte:window bind:innerWidth />

<Tooltip.Provider delayDuration={0} disableHoverableContent={true}>
  <div class="relative flex h-screen" use:theme={$currentTheme}>
    <div
      class="fixed top-0 left-0 right-0 z-30 h-14 backdrop-blur-xl bg-background/50 supports-[backdrop-filter]:bg-background/30 transition-all duration-300"
    ></div>

    <Navbar
      user={user || undefined}
      showLogo={!showSidebar}
      showLoginButton={!user}
      {sidebarWidth}
    >
      {#snippet themeSwitch()}
        <ThemeSwitch
          checked={$isDark}
          on={{ change: (theme) => settings.set('theme', theme) }}
        />
      {/snippet}
    </Navbar>

    <div class="flex w-full h-full">
      {#if showSidebar && user}
        <AppSidebar
          {user}
          collapsed={sidebarCollapsed}
          variant="default"
          width={sidebarWidth}
          onItemSelect={handleSidebarItemSelect}
          onCollapseToggle={handleSidebarCollapseToggle}
        />
      {/if}

      <main id="main" class="flex-1 overflow-y-auto pt-14">
        {@render children?.()}
      </main>
    </div>

    <ToastManager />
  </div>
</Tooltip.Provider>
