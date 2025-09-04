<script lang="ts">
  import { SearchBar } from '@slink/feature/Search';
  import { Button } from '@slink/legacy/UI/Action';
  import { Shourtcut } from '@slink/legacy/UI/Action';
  import { Tooltip, TooltipProvider } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import type { User } from '$lib/auth/Type/User';
  import { usePublicImagesFeed } from '$lib/state/PublicImagesFeed.svelte.js';
  import Icon from '@iconify/svelte';

  interface Props {
    user?: Partial<User>;
    showLogo?: boolean;
    showLoginButton?: boolean;
    showUploadButton?: boolean;
    sidebarWidth?: number;
    themeSwitch?: Snippet;
    children?: Snippet;
  }

  let {
    user,
    showLogo = true,
    showLoginButton = false,
    showUploadButton = true,
    themeSwitch,
    children,
  }: Props = $props();

  let innerWidth = $state(0);
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

<TooltipProvider delayDuration={1000}>
  <div class="h-14 w-full">
    <nav class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
      <div class="flex items-center">
        {#if showLogo}
          <a
            href="/services/client/static"
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
        {@render children?.()}

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
          <Tooltip
            variant="default"
            size="md"
            width="fit"
            side="bottom"
            sideOffset={4}
            withArrow={false}
          >
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
            <div class="flex flex-col items-center gap-2 text-center">
              <span class="text-sm font-medium">Upload shortcut</span>
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
  </div>
</TooltipProvider>
