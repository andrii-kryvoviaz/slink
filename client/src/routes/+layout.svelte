<script lang="ts">
  import '@slink/app.css';
  import { theme } from '@slink/lib/actions/theme';
  import { settings } from '@slink/lib/settings';

  import Icon from '@iconify/svelte';

  import { Button, ThemeSwitch } from '@slink/components/Common';
  import { Navbar } from '@slink/components/Layout';
  import { ToastManager } from '@slink/components/Toast';
  import { UserDropdown } from '@slink/components/User';

  import type { LayoutData } from './$types';

  export let data: LayoutData;

  const currentTheme = settings.get('theme', data.settings.theme);
  const { isDark } = currentTheme;
</script>

<div class="flex h-full flex-col" use:theme={$currentTheme}>
  <Navbar>
    <ThemeSwitch
      slot="themeSwitch"
      checked={$isDark}
      on:change={({ detail }) => settings.set('theme', detail)}
    />

    <div slot="profile" class="max-h-10">
      {#if !data.user}
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
        <UserDropdown user={data.user} isDark={$isDark} />
      {/if}
    </div>
  </Navbar>

  <slot />
  <ToastManager />
</div>
