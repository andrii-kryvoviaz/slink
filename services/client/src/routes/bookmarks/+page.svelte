<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { LoadMoreButton } from '@slink/feature/Action';
  import { BookmarkButton, ImagePlaceholder } from '@slink/feature/Image';
  import { EmptyState, Masonry } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import { FormattedDate } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { BookmarkItem } from '@slink/api/Response/Bookmark/BookmarkResponse';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useUserBookmarksFeed } from '@slink/lib/state/UserBookmarksFeed.svelte';

  const bookmarksFeed = useUserBookmarksFeed();
  bookmarksFeed.reset();

  $effect(() => {
    if (!bookmarksFeed.isDirty) {
      bookmarksFeed.load();
    }
  });

  const handleRemoveBookmark = async (bookmark: BookmarkItem) => {
    await ApiClient.bookmark.removeBookmark(bookmark.image.id);
    bookmarksFeed.removeItem(bookmark);
  };
</script>

<svelte:head>
  <title>Bookmarks | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{
      feed: bookmarksFeed,
      minDisplayTime: 300,
      showDelay: 100,
    }}
  >
    <div class="mb-8">
      <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
        Bookmarks
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Your saved images from the community
      </p>
    </div>

    {#if bookmarksFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={8} />
      </div>
    {:else if bookmarksFeed.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:bookmark-simple-duotone"
          title="No bookmarks yet"
          description="Start exploring and bookmark images you love to find them here later."
          actionText="Explore Images"
          actionHref="/explore"
          variant="blue"
          size="md"
        />
      </div>
    {:else if bookmarksFeed.items.length > 0}
      <Masonry items={bookmarksFeed.items} class="gap-4">
        {#snippet itemTemplate(bookmark)}
          {#if !bookmark.image.available}
            <div
              in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
              class="break-inside-avoid bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden p-8 text-center"
            >
              <Icon
                icon="ph:image-broken"
                class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3"
              />
              <p class="text-gray-500 dark:text-gray-400 text-sm">
                Image no longer available
              </p>
              <button
                class="mt-4 text-sm text-red-500 hover:text-red-600 transition-colors"
                onclick={() => handleRemoveBookmark(bookmark)}
              >
                Remove bookmark
              </button>
            </div>
          {:else}
            {@const image = bookmark.image}
            <div
              in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
              class="group/card break-inside-avoid rounded-xl overflow-hidden bg-white dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200/60 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.1] shadow-sm hover:shadow-lg dark:shadow-black/20 dark:hover:shadow-black/40 transition-all duration-300"
            >
              <a
                href="/explore?post={image.id}"
                class="group/image relative block"
              >
                <ImagePlaceholder
                  uniqueId={image.id}
                  src={`/image/${image.attributes?.fileName}`}
                  metadata={image.metadata ?? {
                    height: 0,
                    width: 0,
                    mimeType: undefined,
                    size: undefined,
                  }}
                  showMetadata={false}
                  showOpenInNewTab={false}
                  rounded={false}
                />

                <div
                  class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"
                ></div>

                <div
                  class="absolute top-3 left-3 flex items-center gap-2 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
                >
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
                  >
                    <Icon icon="ph:eye" class="w-3.5 h-3.5" />
                    <span>{image.attributes?.views}</span>
                  </div>
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
                  >
                    <Icon icon="ph:frame-corners" class="w-3.5 h-3.5" />
                    <span>{image.metadata?.width}×{image.metadata?.height}</span
                    >
                  </div>
                </div>

                <div
                  class="absolute bottom-3 left-3 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
                >
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
                  >
                    <Icon icon="ph:bookmark-simple-fill" class="w-3.5 h-3.5" />
                    <span
                      >Saved <FormattedDate
                        date={bookmark.createdAt.timestamp}
                      /></span
                    >
                  </div>
                </div>

                <div
                  class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/card:opacity-100 transition-all duration-300 pointer-events-none"
                >
                  <div
                    class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center transform scale-75 group-hover/card:scale-100 transition-transform duration-300"
                  >
                    <Icon
                      icon="ph:arrows-out"
                      class="w-6 h-6 text-white drop-shadow-lg"
                    />
                  </div>
                </div>
              </a>

              <div
                class="absolute top-3 right-3 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
              >
                <BookmarkButton
                  imageId={image.id}
                  imageOwnerId={image.owner?.id ?? ''}
                  isBookmarked={true}
                  size="sm"
                  variant="overlay"
                  onBookmarkChange={(isBookmarked: boolean, _count: number) => {
                    if (!isBookmarked) {
                      bookmarksFeed.removeItem(bookmark);
                    }
                  }}
                />
              </div>

              <div class="p-3">
                <div class="flex items-center gap-2.5">
                  {#if image.owner}
                    <UserAvatar size="sm" user={image.owner} />
                  {/if}
                  <div class="flex-1 min-w-0">
                    <p
                      class="font-medium text-gray-900 dark:text-gray-100 text-sm leading-tight truncate"
                    >
                      {image.owner?.displayName}
                    </p>
                    <div
                      class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                    >
                      {#if image.attributes?.createdAt?.timestamp}
                        <FormattedDate
                          date={image.attributes.createdAt.timestamp}
                        />
                      {/if}
                    </div>
                  </div>
                </div>

                {#if image.attributes?.description?.trim()}
                  <p
                    class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-2"
                  >
                    {image.attributes.description}
                  </p>
                {/if}
              </div>
            </div>
          {/if}
        {/snippet}
      </Masonry>

      {#if bookmarksFeed.hasMore}
        <div class="flex justify-center mt-12">
          <LoadMoreButton
            visible={bookmarksFeed.hasMore}
            loading={bookmarksFeed.isLoading}
            onclick={() => bookmarksFeed.nextPage({ debounce: 300 })}
            variant="modern"
            rounded="full"
          >
            {#snippet text()}
              <span>Load More Bookmarks</span>
            {/snippet}
          </LoadMoreButton>
        </div>
      {/if}
    {/if}
  </div>
</main>
