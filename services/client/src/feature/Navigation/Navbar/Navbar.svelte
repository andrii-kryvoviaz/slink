<script lang="ts">
  import { SearchBar } from '@slink/feature/Search';
  import { Shortcut } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import {
    DropdownSimple,
    DropdownSimpleItem,
  } from '@slink/ui/components/dropdown-simple';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import type { Snippet } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import type { User } from '$lib/auth/Type/User';
  import { locale, t } from '$lib/i18n';
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

  const languageOptions = [
    { value: 'zh', labelKey: 'language.zh', icon: 'ph:translate' },
    { value: 'en', labelKey: 'language.en', icon: 'ph:translate' },
  ] as const;
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
      <DropdownSimple
        triggerClass="w-fit"
        contentProps={{ align: 'start', sideOffset: 12 }}
      >
        {#snippet trigger(triggerProps)}
          <button
            {...triggerProps}
            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200/60 bg-white/80 px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:border-gray-300 hover:bg-white dark:border-gray-700/60 dark:bg-gray-900/80 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-800"
            type="button"
            aria-label={$t('language.switch_aria')}
          >
            <Icon icon="ph:translate" class="h-3.5 w-3.5 shrink-0" />
            <span class="hidden sm:block">
              {$t($locale === 'zh' ? 'language.zh' : 'language.en')}
            </span>
            <Icon icon="ph:caret-down" class="h-3 w-3 shrink-0 opacity-70" />
          </button>
        {/snippet}
        {#each languageOptions as option}
          <DropdownSimpleItem on={{ click: () => ($locale = option.value) }}>
            {#snippet icon()}
              <Icon icon={option.icon} class="h-4 w-4" />
            {/snippet}
            {$t(option.labelKey)}
          </DropdownSimpleItem>
        {/each}
      </DropdownSimple>

      <SearchBar
        searchTerm={publicImagesFeed.searchTerm}
        searchBy={publicImagesFeed.searchBy as
          | 'user'
          | 'description'
          | 'hashtag'}
        placeholder={$t('common.search_placeholder')}
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
              {$t('navbar.upload')}
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
              <span class="text-sm font-semibold"
                >{$t('navbar.upload_image')}</span
              >
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {$t('navbar.upload_description')}
            </p>
            <div
              class="flex items-center justify-between pt-1.5 border-t border-slate-200/30 dark:border-slate-700/30"
            >
              <span
                class="text-[10px] uppercase tracking-wider text-muted-foreground/60 font-medium"
              >
                {$t('navbar.shortcut')}
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
        <span>{$t('auth.signin')}</span>
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
              <span class="text-sm font-semibold"
                >{$t('navbar.toggle_theme')}</span
              >
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {$t('navbar.toggle_theme_description')}
            </p>
          </div>
        </HoverCard.Content>
      </HoverCard.Root>
    {/if}
  </div>
</nav>
