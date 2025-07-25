<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import Icon from '@iconify/svelte';

  import type { User } from '@slink/lib/auth/Type/User';
  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import { Button } from '@slink/components/UI/Action';
  import { Shourtcut } from '@slink/components/UI/Action';
  import { SearchBar } from '@slink/components/UI/Search';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  interface Props {
    user?: Partial<User>;
    showLogo?: boolean;
    showLoginButton?: boolean;
    showUploadButton?: boolean;
    sidebarWidth?: number;
    themeSwitch?: import('svelte').Snippet;
    useFlexLayout?: boolean;
  }

  let {
    user,
    showLogo = true,
    showLoginButton = false,
    showUploadButton = true,
    sidebarWidth = 0,
    themeSwitch,
    useFlexLayout = false,
  }: Props = $props();

  let innerWidth = $state(0);
  let isMobile = $derived(innerWidth < 768);
  let navLeftPosition = $derived(isMobile ? 0 : sidebarWidth);
  let isExplorePage = $derived($page.route.id === '/explore');

  const publicImagesFeed = usePublicImagesFeed();

  function handleSearch(event: { searchTerm: string; searchBy: string }) {
    const { searchTerm, searchBy } = event;
    publicImagesFeed.search(searchTerm, searchBy);
  }

  function handleClearSearch() {
    publicImagesFeed.resetSearch();
    publicImagesFeed.load();
  }

  function handleUploadShortcut() {
    goto('/upload');
  }
</script>

<svelte:window bind:innerWidth />

<header
  class={useFlexLayout
    ? 'h-14 bg-background border-b border-bc-header'
    : 'fixed top-0 right-0 z-50 h-14 bg-background border-b border-bc-header'}
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
          <span
            class="font-semibold text-foreground tracking-tight text-lg hidden sm:inline-block"
          >
            Slink</span
          >
        </a>
      {/if}
    </div>

    <div class="flex items-center gap-3">
      {#if isExplorePage}
        <SearchBar
          searchTerm={publicImagesFeed.searchTerm}
          searchBy={publicImagesFeed.searchBy as
            | 'user'
            | 'description'
            | 'hashtag'}
          placeholder="Search images..."
          disabled={publicImagesFeed.isLoading}
          onsearch={handleSearch}
          onclear={handleClearSearch}
        />
      {/if}
      {#if showUploadButton}
        <Tooltip side="left" sideOffset={8}>
          {#snippet trigger()}
            <Button
              href="/upload"
              variant="glass"
              size="sm"
              rounded="full"
              id="uploadImageLink"
              class="flex flex-row gap-2"
            >
              <Icon icon="ph:plus-fill" class="h-3 w-3 sm:h-4 sm:w-4" />
              Upload
            </Button>
          {/snippet}
          <div class="flex items-center gap-2">
            <span>Shortcut</span>
            <Shourtcut
              control={true}
              shift={true}
              key="u"
              onHit={handleUploadShortcut}
              size="sm"
            />
          </div>
        </Tooltip>
      {/if}

      {#if showLoginButton}
        <Button
          href="/profile/login"
          variant="glass"
          size="sm"
          rounded="full"
          class="flex items-center gap-2"
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
