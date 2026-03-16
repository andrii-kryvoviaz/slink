<script lang="ts">
  import { SearchBar } from '@slink/feature/Search';
  import { Shortcut } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import type { Snippet } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
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
    showLogo = true,
    showLoginButton = false,
    showUploadButton = true,
    themeSwitch,
    children,
  }: Props = $props();

  let innerWidth = $state(0);
  let isExplorePage = $derived(page.route.id === '/explore');

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

<nav
  class="flex flex-grow h-14 items-center justify-between px-4 sm:px-6 lg:px-8"
>
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

  <div class="flex items-center gap-3 -ml-4">
    {@render children?.()}

    {#if isExplorePage}
      <SearchBar
        searchTerm={publicImagesFeed.searchTerm}
        searchBy={publicImagesFeed.searchBy as
          | 'user'
          | 'description'
          | 'hashtag'}
        placeholder="Search images..."
        onsearch={handleSearch}
        onclear={handleClearSearch}
      />
    {/if}
    {#if showUploadButton}
      <HoverCard.Root openDelay={1000} closeDelay={200}>
        <HoverCard.Trigger>
          {#snippet child({ props })}
            <Button
              {...props}
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
        </HoverCard.Trigger>
        <HoverCard.Content
          variant="glass"
          size="sm"
          rounded="xl"
          width="auto"
          side="bottom"
          sideOffset={8}
          align="end"
          class="min-w-52 max-w-xs"
        >
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <Icon
                icon="ph:cloud-arrow-up"
                class="h-4 w-4 text-blue-500 dark:text-blue-400"
              />
              <span class="text-sm font-semibold">Upload Image</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Share your media with others by uploading them and organizing them
              into collections.
            </p>
            <div
              class="flex items-center justify-between pt-1.5 border-t border-slate-200/30 dark:border-slate-700/30"
            >
              <span
                class="text-[10px] uppercase tracking-wider text-muted-foreground/60 font-medium"
              >
                Shortcut
              </span>
              <Shortcut control={true} shift={true} key="u" size="sm" />
            </div>
          </div>
        </HoverCard.Content>
      </HoverCard.Root>
      <Shortcut
        control={true}
        shift={true}
        key="u"
        onHit={handleUploadShortcut}
        hidden={true}
      />
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
      <HoverCard.Root openDelay={1000} closeDelay={200}>
        <HoverCard.Trigger>
          <div class="flex items-center">
            {@render themeSwitch?.()}
          </div>
        </HoverCard.Trigger>
        <HoverCard.Content
          variant="glass"
          size="sm"
          rounded="xl"
          width="auto"
          side="bottom"
          sideOffset={8}
          align="end"
          class="min-w-52"
        >
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <Icon
                icon="ph:palette"
                class="h-4 w-4 text-blue-500 dark:text-blue-400"
              />
              <span class="text-sm font-semibold">Toggle Theme</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              Switch between light and dark mode.
            </p>
          </div>
        </HoverCard.Content>
      </HoverCard.Root>
    {/if}
  </div>
</nav>
