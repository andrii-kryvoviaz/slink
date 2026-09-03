<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { BookmarkButton, ImagePlaceholder } from '@slink/feature/Image';
  import {
    EmptyState,
    GhostGrid,
    Masonry,
    hintIconVariants,
  } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import { FormattedDate, Subtitle, Title } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { BookmarkItem } from '@slink/api/Response/Bookmark/BookmarkResponse';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useUserBookmarksFeed } from '@slink/lib/state/UserBookmarksFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const bookmarksFeed = useUserBookmarksFeed();
  bookmarksFeed.reset();
  bookmarksFeed.hydrate({ hasItems: data.hasAny });

  $effect(() => {
    if (untrack(() => bookmarksFeed.needsLoad)) {
      bookmarksFeed.load();
    }
  });

  const handleRemoveBookmark = (bookmark: BookmarkItem) =>
    bookmarksFeed.removeBookmark(bookmark);
</script>

<svelte:head>
  <title>Bookmarks | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed: bookmarksFeed }}
  >
    <div class="mb-8">
      <Title>Bookmarks</Title>
      <Subtitle>Your saved images from the community</Subtitle>
    </div>

    {#if bookmarksFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={8} />
      </div>
    {:else if bookmarksFeed.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          kind="first-use"
          title="No bookmarks yet"
          description="Images you bookmark are collected here, ready when you need them."
        >
          {#snippet preview()}
            <GhostGrid />
          {/snippet}
          {#snippet action()}
            <Button variant="primary" size="md" rounded="lg" href="/explore">
              <Icon icon="lucide:search" class="h-4 w-4" />
              Explore images
            </Button>
          {/snippet}
          {#snippet hint()}
            <span class={hintIconVariants()}>
              <Icon icon="ph:bookmark-simple" class="h-3 w-3" />
            </span>
            Tap the bookmark on any image to save it here
          {/snippet}
        </EmptyState>
      </div>
    {:else if bookmarksFeed.items.length > 0}
      <Masonry items={bookmarksFeed.items} class="gap-4">
        {#snippet itemTemplate(bookmark)}
          {#if !('url' in bookmark.image)}
            <div
              in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
              class="break-inside-avoid bg-muted rounded-2xl border border-border overflow-hidden p-8 text-center"
            >
              <Icon
                icon="ph:image-broken"
                class="w-12 h-12 mx-auto text-ring mb-3"
              />
              <p class="text-foreground-muted text-sm">
                Image no longer available
              </p>
              <button
                class="mt-4 text-sm text-danger hover:text-danger-strong transition-colors"
                onclick={() => handleRemoveBookmark(bookmark)}
              >
                Remove bookmark
              </button>
            </div>
          {:else}
            {@const image = bookmark.image}
            <div
              in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
              class="group/card break-inside-avoid rounded-xl overflow-hidden bg-card/80 backdrop-blur-sm border border-border/30 hover:border-border/50 shadow-sm hover:shadow-lg dark:shadow-scrim/20 dark:hover:shadow-scrim/40 transition-all duration-300"
            >
              <a
                href="/explore?post={image.id}"
                class="group/image relative block"
              >
                <ImagePlaceholder
                  uniqueId={image.id}
                  src={image.url}
                  metadata={image.metadata}
                  showMetadata={false}
                  showOpenInNewTab={false}
                  rounded={false}
                />

                <div
                  class="absolute inset-0 bg-gradient-to-t from-scrim/60 via-transparent to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"
                ></div>

                <div
                  class="absolute top-3 left-3 flex items-center gap-2 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
                >
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-scrim/40 backdrop-blur-md text-on-surface-inverse text-xs"
                  >
                    <Icon icon="ph:eye" class="w-3.5 h-3.5" />
                    <span>{image.attributes.views}</span>
                  </div>
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-scrim/40 backdrop-blur-md text-on-surface-inverse text-xs"
                  >
                    <Icon icon="ph:frame-corners" class="w-3.5 h-3.5" />
                    <span>{image.metadata.width}×{image.metadata.height}</span>
                  </div>
                </div>

                <div
                  class="absolute bottom-3 left-3 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
                >
                  <div
                    class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-scrim/40 backdrop-blur-md text-on-surface-inverse text-xs"
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
                    class="w-12 h-12 rounded-full bg-on-surface-inverse/20 backdrop-blur-sm flex items-center justify-center transform scale-75 group-hover/card:scale-100 transition-transform duration-300"
                  >
                    <Icon
                      icon="ph:arrows-out"
                      class="w-6 h-6 text-on-surface-inverse drop-shadow-lg"
                    />
                  </div>
                </div>
              </a>

              <div
                class="absolute top-3 right-3 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
              >
                <BookmarkButton
                  imageId={image.id}
                  imageOwnerId={image.owner.id}
                  isBookmarked={true}
                  size="sm"
                  variant="overlay"
                  onBookmarkChange={(isBookmarked: boolean) =>
                    bookmarksFeed.applyBookmarkChange(bookmark, isBookmarked)}
                />
              </div>

              <div class="p-3">
                <div class="flex items-center gap-2.5">
                  <UserAvatar size="sm" user={image.owner} />
                  <div class="flex-1 min-w-0">
                    <p
                      class="font-medium text-foreground text-sm leading-tight truncate"
                    >
                      {image.owner.displayName}
                    </p>
                    <div class="text-xs text-foreground-muted mt-0.5">
                      {#if image.attributes.createdAt.timestamp}
                        <FormattedDate
                          date={image.attributes.createdAt.timestamp}
                        />
                      {/if}
                    </div>
                  </div>
                </div>

                {#if image.attributes.description.trim()}
                  <p
                    class="mt-3 text-sm text-foreground-muted leading-relaxed line-clamp-2"
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
          />
        </div>
      {/if}
    {/if}
  </div>
</main>
