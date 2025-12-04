<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { BookmarkButton, ImagePlaceholder } from '@slink/feature/Image';
  import { EmptyState, Masonry } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import { Badge, FormattedDate, TextEllipsis } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
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
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{
      feed: bookmarksFeed,
      minDisplayTime: 300,
      showDelay: 100,
    }}
  >
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        Bookmarks
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1">
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
      <Masonry items={bookmarksFeed.items} class="gap-6">
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
              class="group/card break-inside-avoid bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden transition-all duration-300"
            >
              <div class="group/header p-5 pb-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    {#if image.owner}
                      <UserAvatar size="md" user={image.owner} />
                    {/if}
                    <div class="flex-1 min-w-0">
                      <TextEllipsis
                        class="font-medium text-gray-900 dark:text-white text-sm"
                      >
                        {image.owner?.displayName}
                      </TextEllipsis>
                      <div class="text-xs text-gray-500 dark:text-gray-400">
                        {#if image.attributes?.createdAt?.timestamp}
                          <FormattedDate
                            date={image.attributes.createdAt.timestamp}
                          />
                        {/if}
                      </div>
                    </div>
                  </div>
                  <BookmarkButton
                    imageId={image.id}
                    imageOwnerId={image.owner?.id ?? ''}
                    isBookmarked={true}
                    size="sm"
                    onBookmarkChange={(
                      isBookmarked: boolean,
                      _count: number,
                    ) => {
                      if (!isBookmarked) {
                        bookmarksFeed.removeItem(bookmark);
                      }
                    }}
                  />
                </div>
              </div>

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
                  class="absolute inset-0 bg-black/0 group-hover/image:bg-black/30 transition-all duration-300 flex items-center justify-center pointer-events-none"
                >
                  <div
                    class="opacity-0 group-hover/image:opacity-100 transform scale-90 group-hover/image:scale-100 transition-all duration-300"
                  >
                    <Icon
                      icon="heroicons:arrows-pointing-out"
                      class="w-8 h-8 text-white drop-shadow-lg"
                    />
                  </div>
                </div>
              </a>

              <div
                class="group/footer flex flex-col justify-between gap-2 py-2 px-5"
              >
                <div
                  class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                >
                  <div class="flex items-center gap-2">
                    <Badge variant="glass" size="xs">
                      <Icon icon="heroicons:eye" class="w-3 h-3 mr-1" />
                      {image.attributes?.views}
                    </Badge>
                    <Badge variant="glass" size="xs">
                      <Icon icon="heroicons:photo" class="w-3 h-3 mr-1" />
                      {image.metadata?.width}×{image.metadata?.height}
                    </Badge>
                  </div>
                  <span class="text-xs text-gray-400">
                    Saved <FormattedDate date={bookmark.createdAt.timestamp} />
                  </span>
                </div>

                {#if image.attributes?.description?.trim()}
                  <div
                    class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed line-clamp-2"
                  >
                    {image.attributes.description}
                  </div>
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
