<script lang="ts">
  import type { User } from '@slink/lib/auth/Type/User';

  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';

  interface Props {
    user?: Partial<User>;
    showLogo?: boolean;
    showLoginButton?: boolean;
    sidebarWidth?: number;
    themeSwitch?: import('svelte').Snippet;
  }

  let {
    user,
    showLogo = true,
    showLoginButton = false,
    sidebarWidth = 0,
    themeSwitch,
  }: Props = $props();

  let innerWidth = $state(0);
  let isMobile = $derived(innerWidth < 768);
  let navLeftPosition = $derived(isMobile ? 0 : sidebarWidth);
</script>

<svelte:window bind:innerWidth />

<header
  class="fixed top-0 right-0 z-40 h-14 transition-all duration-300"
  style:left="{navLeftPosition}px"
>
  <nav class="flex h-14 items-center justify-between px-4 sm:px-6">
    <div class="flex items-center">
      {#if showLogo}
        <a
          href="/"
          class="flex items-center gap-3 hover:opacity-80 transition-opacity duration-150"
        >
          <div
            class="flex items-center justify-center w-8 h-8 rounded-xl bg-muted/20 border-0 hover:bg-muted/30 hover:scale-105 transition-all duration-200 cursor-pointer"
          >
            <img class="h-5 w-5" src="/favicon.png" alt="Slink" />
          </div>
          <span class="font-semibold text-foreground tracking-tight">Slink</span
          >
        </a>
      {/if}
    </div>

    <div class="flex items-center gap-2 ml-auto">
      {#if !showLoginButton}
        <Button
          href="/upload"
          variant="dark"
          size="sm"
          rounded="full"
          motion="hover:opacity"
          id="uploadImageLink"
          class="flex flex-row gap-2 py-3 text-sm hover:no-underline sm:py-2 sm:text-xs"
        >
          <Icon icon="ph:plus-fill" class="h-3 w-3 sm:h-4 sm:w-4" />
          Upload
        </Button>
      {/if}

      {#if showLoginButton}
        <Button
          href="/profile/login"
          variant="modern"
          size="sm"
          rounded="full"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-all duration-200"
        >
          <Icon icon="ph:sign-in-bold" class="h-4 w-4" />
          <span>Sign In</span>
        </Button>
      {/if}

      {#if themeSwitch}
        <div class="flex items-center ml-1">
          {@render themeSwitch?.()}
        </div>
      {/if}
    </div>
  </nav>
</header>
