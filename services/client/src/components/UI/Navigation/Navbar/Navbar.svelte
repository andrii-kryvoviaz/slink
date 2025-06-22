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
    useFlexLayout?: boolean;
  }

  let {
    user,
    showLogo = true,
    showLoginButton = false,
    sidebarWidth = 0,
    themeSwitch,
    useFlexLayout = false,
  }: Props = $props();

  let innerWidth = $state(0);
  let isMobile = $derived(innerWidth < 768);
  let navLeftPosition = $derived(isMobile ? 0 : sidebarWidth);
</script>

<svelte:window bind:innerWidth />

<header
  class={useFlexLayout
    ? 'h-14 backdrop-blur-xl bg-background/95 supports-[backdrop-filter]:bg-background/80 border-b border-bc-header'
    : 'fixed top-0 right-0 z-50 h-14 backdrop-blur-xl bg-background/95 supports-[backdrop-filter]:bg-background/80 border-b border-bc-header'}
  style:left={useFlexLayout ? undefined : `${navLeftPosition}px`}
>
  <nav class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center">
      {#if showLogo}
        <a
          href="/"
          class="flex items-center gap-3 hover:opacity-80 transition-opacity duration-200 group"
        >
          <div
            class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 group-hover:border-primary/20 group-hover:scale-105 transition-all duration-200"
          >
            <img class="h-5 w-5" src="/favicon.png" alt="Slink" />
          </div>
          <span class="font-semibold text-foreground tracking-tight text-lg"
            >Slink</span
          >
        </a>
      {/if}
    </div>

    <div class="flex items-center gap-3 ml-auto">
      {#if !showLoginButton && user}
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
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium shadow-sm hover:shadow-md transition-all duration-200"
        >
          <Icon icon="ph:sign-in" class="h-4 w-4" />
          <span>Sign In</span>
        </Button>
      {/if}

      {#if themeSwitch}
        <div class="flex items-center">
          {@render themeSwitch?.()}
        </div>
      {/if}
    </div>
  </nav>
</header>
