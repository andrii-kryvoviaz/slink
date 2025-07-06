<script lang="ts">
  import { Tooltip } from 'bits-ui';

  import '@slink/app.css';

  import { theme } from '@slink/lib/actions/theme';
  import { settings } from '@slink/lib/settings';
  import { useGlobalSettings } from '@slink/lib/state/GlobalSettings.svelte';
  import { initResponsiveStore } from '@slink/lib/stores/responsive.svelte';

  import { ThemeSwitch } from '@slink/components/UI/Action';
  import { AppSidebar, Navbar } from '@slink/components/UI/Navigation';
  import { ScrollArea } from '@slink/components/UI/ScrollArea';
  import { ToastManager } from '@slink/components/UI/Toast';

  let { data, children } = $props();
  let user = $derived(data.user);
  let sidebarGroups = $derived(data.sidebarGroups || []);

  const globalSettingsManager = useGlobalSettings();

  $effect(() => {
    globalSettingsManager.initialize(data.globalSettings);
  });

  const currentTheme = settings.get('theme', data.settings.theme);
  const { isDark } = currentTheme;

  let showSidebar = $derived(!!user);

  initResponsiveStore();
</script>

<Tooltip.Provider delayDuration={0} disableHoverableContent={true}>
  <div class="relative flex h-screen" use:theme={$currentTheme}>
    {#if showSidebar}
      <AppSidebar
        user={user || undefined}
        groups={sidebarGroups}
        variant="default"
        defaultExpanded={data.settings.sidebar?.expanded ?? true}
      />
    {/if}

    <div class="flex flex-col flex-1 min-w-0">
      <Navbar
        user={user || undefined}
        showLogo={!showSidebar}
        showLoginButton={!user}
        showUploadButton={!!user ||
          !!data.globalSettings?.access?.allowGuestUploads}
        useFlexLayout={true}
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

      <ScrollArea
        class="flex-1"
        viewportClass="pl-[max(env(safe-area-inset-left),0px)]"
        variant="default"
        type="scroll"
        hideDelay={1000}
        scrollbarSize="md"
      >
        {@render children?.()}
      </ScrollArea>
    </div>

    <ToastManager />
  </div>
</Tooltip.Provider>
