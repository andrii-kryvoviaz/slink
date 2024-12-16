<script lang="ts">
  import { Tooltip } from 'bits-ui';

  import '@slink/app.css';

  import Icon from '@iconify/svelte';

  import { theme } from '@slink/lib/actions/theme';
  import { settings } from '@slink/lib/settings';

  import { UserDropdown } from '@slink/components/Feature/User';
  import { Button, ThemeSwitch } from '@slink/components/UI/Action';
  import { Navbar } from '@slink/components/UI/Navigation';
  import { ToastManager } from '@slink/components/UI/Toast';

  let { data, children } = $props();

  // ToDo: find a way to make data reactive as it was in Svelte 4
  let user = $derived(data.user);

  const currentTheme = settings.get('theme', data.settings.theme);
  const { isDark } = currentTheme;
</script>

<Tooltip.Provider delayDuration={0} disableHoverableContent={true}>
  <div class="flex h-full flex-col" use:theme={$currentTheme}>
    <Navbar>
      {#snippet themeSwitch()}
        <ThemeSwitch
          checked={$isDark}
          on={{ change: (theme) => settings.set('theme', theme) }}
        />
      {/snippet}

      {#snippet profile()}
        <div class="max-h-10">
          {#if !user}
            <Button
              href="/profile/login"
              motion="hover:opacity"
              variant="link"
              class="p-0 hover:no-underline"
            >
              <span class="text-sm font-semibold leading-6">
                <span>Log in</span>
                <Icon icon="solar:login-broken" class="inline h-6 w-6" />
              </span>
            </Button>
          {:else}
            <UserDropdown {user} isDark={$isDark} />
          {/if}
        </div>
      {/snippet}
    </Navbar>

    {@render children?.()}
    <ToastManager />
  </div>
</Tooltip.Provider>
