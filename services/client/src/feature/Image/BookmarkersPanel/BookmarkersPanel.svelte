<script lang="ts">
  import { UserAvatar } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { slide } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    BookmarkerItem,
    BookmarkersResponse,
  } from '@slink/api/Response';

  import { formatDate } from '@slink/lib/utils/date';

  import {
    bookmarkersPanelChevronTheme,
    bookmarkersPanelContainerTheme,
    bookmarkersPanelEmptyTheme,
    bookmarkersPanelHeaderTheme,
    bookmarkersPanelIconTheme,
    bookmarkersPanelIconWrapperTheme,
    bookmarkersPanelItemDateTheme,
    bookmarkersPanelItemNameTheme,
    bookmarkersPanelItemTheme,
    bookmarkersPanelLabelTheme,
    bookmarkersPanelListTheme,
    bookmarkersPanelValueTheme,
  } from './BookmarkersPanel.theme';

  interface Props {
    imageId: string;
    count: number;
  }

  const ITEMS_PER_PAGE = 12;

  let { imageId, count }: Props = $props();

  let isExpanded = $state(false);
  let pages = $state<Map<number, BookmarkerItem[]>>(new Map());
  let currentPage = $state(1);
  let totalPages = $state(1);
  let cursor = $state<string | undefined>(undefined);

  const label = $derived(count === 1 ? 'time' : 'times');
  const currentPageItems = $derived(pages.get(currentPage) ?? []);
  const hasPrev = $derived(currentPage > 1);
  const hasNext = $derived(currentPage < totalPages);

  const {
    isLoading,
    error,
    data: response,
    run: fetchBookmarkers,
  } = ReactiveState<BookmarkersResponse>(
    (cursorValue?: string) => {
      return ApiClient.bookmark.getImageBookmarkers(
        imageId,
        ITEMS_PER_PAGE,
        cursorValue,
      );
    },
    { minExecutionTime: 200 },
  );

  const handleToggle = async () => {
    isExpanded = !isExpanded;

    if (isExpanded && pages.size === 0) {
      await loadPage(1);
    }
  };

  const loadPage = async (page: number) => {
    if (pages.has(page)) return;

    await fetchBookmarkers(page === 1 ? undefined : cursor);

    if ($response) {
      pages.set(page, $response.data);
      pages = new Map(pages);
      totalPages = Math.ceil($response.meta.total / ITEMS_PER_PAGE);
      cursor = $response.meta.nextCursor;
    }
  };

  const handlePrev = () => {
    if (!hasPrev || $isLoading) return;
    currentPage -= 1;
  };

  const handleNext = async () => {
    if (!hasNext || $isLoading) return;
    currentPage += 1;
    await loadPage(currentPage);
  };
</script>

{#if count > 0}
  <div class={bookmarkersPanelContainerTheme()}>
    <button
      type="button"
      class={bookmarkersPanelHeaderTheme()}
      onclick={handleToggle}
      aria-expanded={isExpanded}
    >
      <div class={bookmarkersPanelIconWrapperTheme()}>
        <Icon
          icon="ph:bookmark-simple-fill"
          class={bookmarkersPanelIconTheme()}
        />
      </div>
      <div class="flex flex-col min-w-0 flex-1 text-left">
        <span class={bookmarkersPanelLabelTheme()}>Bookmarked</span>
        <span class={bookmarkersPanelValueTheme()}>{count} {label}</span>
      </div>
      {#if $isLoading && pages.size === 0}
        <Icon
          icon="svg-spinners:ring-resize"
          class="w-5 h-5 text-indigo-500 dark:text-indigo-400"
        />
      {:else}
        <Icon
          icon="lucide:chevron-right"
          class={bookmarkersPanelChevronTheme()}
          style="transform: rotate({isExpanded ? 90 : 0}deg)"
        />
      {/if}
    </button>

    {#if isExpanded}
      <div
        class={bookmarkersPanelListTheme()}
        transition:slide={{ duration: 200 }}
      >
        {#if currentPageItems.length === 0 && !$isLoading}
          <div class={bookmarkersPanelEmptyTheme()}>No bookmarks yet</div>
        {:else}
          {#if totalPages > 1}
            <div
              class="flex items-center justify-between px-4 py-2 border-b border-indigo-100/50 dark:border-indigo-500/10"
            >
              <button
                type="button"
                class="p-1.5 rounded-md text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/30 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                onclick={handlePrev}
                disabled={!hasPrev || $isLoading}
              >
                <Icon icon="lucide:chevron-left" class="w-4 h-4" />
              </button>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {#if $isLoading}
                  <Icon
                    icon="svg-spinners:ring-resize"
                    class="w-3 h-3 inline"
                  />
                {:else}
                  {currentPage} / {totalPages}
                {/if}
              </span>
              <button
                type="button"
                class="p-1.5 rounded-md text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/30 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                onclick={handleNext}
                disabled={!hasNext || $isLoading}
              >
                <Icon icon="lucide:chevron-right" class="w-4 h-4" />
              </button>
            </div>
          {/if}

          {#each currentPageItems as bookmarker (bookmarker.id)}
            <div class={bookmarkersPanelItemTheme()}>
              <UserAvatar
                user={{
                  displayName: bookmarker.displayName,
                  email: bookmarker.email,
                }}
                size="sm"
              />
              <div class="flex flex-col min-w-0 flex-1">
                <span class={bookmarkersPanelItemNameTheme()}>
                  {bookmarker.displayName}
                </span>
                <span class={bookmarkersPanelItemDateTheme()}>
                  {formatDate(bookmarker.bookmarkedAt.formattedDate)}
                </span>
              </div>
            </div>
          {/each}
        {/if}

        {#if $error}
          <div class="px-4 py-3 text-sm text-red-500 dark:text-red-400">
            Failed to load bookmarks
          </div>
        {/if}
      </div>
    {/if}
  </div>
{/if}
