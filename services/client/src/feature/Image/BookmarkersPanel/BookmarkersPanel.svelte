<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { AvatarStack, UserAvatar } from '@slink/feature/User';
  import { ScrollArea } from '@slink/ui/components/scroll-area';
  import { StatDisclosure } from '@slink/ui/components/stat-disclosure';
  import { onMount } from 'svelte';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    BookmarkerItem,
    BookmarkersResponse,
  } from '@slink/api/Response';

  import { formatDate } from '@slink/lib/utils/date.svelte';

  import {
    bookmarkersPanelEmptyTheme,
    bookmarkersPanelErrorTheme,
    bookmarkersPanelItemDateTheme,
    bookmarkersPanelItemNameTheme,
    bookmarkersPanelItemTheme,
    bookmarkersPanelListTheme,
    bookmarkersPanelMoreTheme,
  } from './BookmarkersPanel.theme';

  interface Props {
    imageId: string;
    count: number;
  }

  const ITEMS_PER_PAGE = 12;

  let { imageId, count }: Props = $props();

  let items = $state<BookmarkerItem[]>([]);
  let total = $state<number | undefined>(undefined);
  let cursor = $state<string | undefined>(undefined);

  const displayCount = $derived(total ?? count);
  const remaining = $derived(total === undefined ? 0 : total - items.length);

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

  const loadMore = async () => {
    await fetchBookmarkers(cursor);

    if (!$response) return;

    items = [...items, ...$response.data];
    total = $response.meta.total;
    cursor = $response.meta.nextCursor;
  };

  onMount(() => {
    if (count > 0) loadMore();
  });
</script>

{#if displayCount > 0}
  <StatDisclosure
    icon="ph:bookmark-simple-fill"
    label="Bookmarked"
    value={plural(displayCount, ['By # person', 'By # people'])}
  >
    {#snippet trailing()}
      {#if items.length > 0}
        <AvatarStack
          users={items}
          ringClass="ring-indigo-50 dark:ring-indigo-950"
        />
      {/if}
    {/snippet}

    <ScrollArea maxHeight="lg" orientation="vertical" type="scroll">
      <div class={bookmarkersPanelListTheme()}>
        {#if $error}
          <div class={bookmarkersPanelErrorTheme()}>
            Failed to load bookmarks
          </div>
        {:else if $isLoading && items.length === 0}
          <div class={bookmarkersPanelEmptyTheme()}>
            <Icon icon="svg-spinners:ring-resize" class="w-4 h-4 inline" />
          </div>
        {:else if items.length === 0}
          <div class={bookmarkersPanelEmptyTheme()}>No bookmarks yet</div>
        {:else}
          {#each items as bookmarker (bookmarker.id)}
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
          {#if remaining > 0}
            <button
              type="button"
              class={bookmarkersPanelMoreTheme()}
              onclick={loadMore}
              disabled={$isLoading}
            >
              {#if $isLoading}
                <Icon icon="svg-spinners:ring-resize" class="w-4 h-4" />
              {:else}
                Show {remaining} more
              {/if}
            </button>
          {/if}
        {/if}
      </div>
    </ScrollArea>
  </StatDisclosure>
{/if}
