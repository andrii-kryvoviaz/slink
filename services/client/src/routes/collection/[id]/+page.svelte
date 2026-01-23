<script lang="ts">
  import { LoadMoreButton, StopPropagation } from '@slink/feature/Action';
  import {
    CollectionItemDropdown,
    ShareCollectionPopover,
  } from '@slink/feature/Collection';
  import {
    DimensionsBadge,
    DownloadButton,
    ImagePlaceholder,
    PostViewer,
    ViewCountBadge,
  } from '@slink/feature/Image';
  import BookmarkButton from '@slink/feature/Image/BookmarkButton/BookmarkButton.svelte';
  import { EmptyState, Masonry } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import {
    CopyContainer,
    EditableText,
    ExpandableText,
    FormattedDate,
  } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import {
    Popover,
    PopoverContent,
    PopoverTrigger,
  } from '@slink/ui/components/popover';
  import { untrack } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { CollectionItem } from '@slink/api/Response';
  import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';
  import type { AuthenticatedUser } from '@slink/api/Response/User/AuthenticatedUser';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { CollectionImagesFeedAdapter } from '@slink/lib/state/CollectionImagesFeedAdapter';
  import { useCollectionItemsFeed } from '@slink/lib/state/CollectionItemsFeed.svelte';
  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  interface Props {
    data: {
      collectionId: string;
      user: AuthenticatedUser | null;
    };
  }

  let { data }: Props = $props();

  const itemsFeed = useCollectionItemsFeed();
  const postViewerState = usePostViewerState();
  itemsFeed.reset();

  const isOwner = $derived(
    data.user !== null &&
      itemsFeed.collection !== null &&
      itemsFeed.collection.userId === data.user?.id,
  );

  $effect(() => {
    const collectionId = data.collectionId;

    untrack(() => {
      itemsFeed.setCollectionId(collectionId);
      if (!itemsFeed.isDirty) {
        itemsFeed.load();
      }
    });
  });

  const feedAdapter = new CollectionImagesFeedAdapter(itemsFeed);
  postViewerState.setFeed(feedAdapter as any);

  $effect(() => {
    if (!postViewerState.isOpen && itemsFeed.isDirty) {
      postViewerState.openFromUrlAsync();
    }
  });

  let shareInfo: ShareResponse | null = $state(null);
  let sharePopoverOpen = $state(false);
  let removingItems: Set<string> = $state(new Set());
  let isSharing = $state(false);
  let isSavingName = $state(false);
  let isSavingDescription = $state(false);

  $effect(() => {
    if (itemsFeed.collection?.shareInfo) {
      shareInfo = itemsFeed.collection.shareInfo;
    }
  });

  const openPostViewer = (imageId: string) => {
    const index = itemsFeed.getItemIndex(imageId);
    if (index !== -1) {
      postViewerState.open(index);
    }
  };

  const handleShareConfirm = async () => {
    isSharing = true;
    shareInfo = await itemsFeed.share();
    isSharing = false;
    sharePopoverOpen = false;
  };

  const handleRemoveItem = async (item: CollectionItem) => {
    removingItems.add(item.itemId);
    removingItems = new Set(removingItems);
    await itemsFeed.removeItemFromCollection(item.itemId);
    removingItems.delete(item.itemId);
    removingItems = new Set(removingItems);
  };

  const handleUpdateName = async (name: string) => {
    isSavingName = true;
    try {
      await itemsFeed.updateDetails({ name });
    } catch (error: unknown) {
      printErrorsAsToastMessage(error as Error);
    } finally {
      isSavingName = false;
    }
  };

  const handleUpdateDescription = async (description: string) => {
    isSavingDescription = true;
    try {
      await itemsFeed.updateDetails({ description });
    } catch (error: unknown) {
      printErrorsAsToastMessage(error as Error);
    } finally {
      isSavingDescription = false;
    }
  };
</script>

<svelte:head>
  <title>{itemsFeed.collection?.name ?? 'Collection'} | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{
      feed: itemsFeed,
      minDisplayTime: 300,
      showDelay: 100,
    }}
  >
    <div class="mb-8">
      {#if isOwner}
        <a
          href="/collections"
          class="group inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors duration-200 mb-4"
        >
          <Icon
            icon="ph:arrow-left"
            class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-0.5"
          />
          Back to Collections
        </a>
      {/if}

      {#if itemsFeed.collection}
        <div
          class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-3">
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800"
              >
                <Icon
                  icon="ph:folder-simple-duotone"
                  class="h-5 w-5 text-gray-600 dark:text-gray-400"
                />
              </div>
              <div class="min-w-0 flex-1">
                {#if isOwner}
                  <EditableText
                    value={itemsFeed.collection.name}
                    type="input"
                    placeholder="Collection name..."
                    emptyText="Add a name..."
                    isLoading={isSavingName}
                    showActions={false}
                    class="[&_button]:py-1 [&_button]:px-2 [&_button]:-mx-2 [&_input]:py-1 [&_input]:px-2 [&_input]:text-lg [&_input]:font-semibold [&_span]:text-lg [&_span]:font-semibold [&_span]:text-gray-900 [&_span]:dark:text-white"
                    on={{ change: handleUpdateName }}
                  />
                {:else}
                  <h1
                    class="text-lg font-semibold text-gray-900 dark:text-white truncate"
                  >
                    {itemsFeed.collection.name}
                  </h1>
                {/if}
                <div
                  class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                >
                  <span
                    >{itemsFeed.collection.itemCount ?? itemsFeed.items.length} items</span
                  >
                  <span class="text-gray-300 dark:text-gray-600">·</span>
                  <FormattedDate
                    date={itemsFeed.collection.createdAt.timestamp}
                  />
                </div>
              </div>
            </div>
            {#if isOwner}
              <EditableText
                value={itemsFeed.collection.description ?? ''}
                type="textarea"
                placeholder="Add a description..."
                emptyText="Click to add a description..."
                isLoading={isSavingDescription}
                class="mt-3 ml-13"
                on={{ change: handleUpdateDescription }}
              />
            {:else if itemsFeed.collection.description}
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 ml-13">
                {itemsFeed.collection.description}
              </p>
            {/if}
          </div>
          {#if isOwner}
            <div class="shrink-0 h-10 flex items-center">
              {#if shareInfo}
                <div class="relative flex items-center">
                  <span
                    class="absolute -top-4 left-0 text-[10px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500"
                  >
                    Share Link
                  </span>
                  <CopyContainer
                    value={shareInfo.shareUrl}
                    size="sm"
                    variant="default"
                  />
                </div>
              {:else}
                <Popover bind:open={sharePopoverOpen}>
                  <PopoverTrigger>
                    <Button
                      variant="glass"
                      size="sm"
                      rounded="full"
                      disabled={isSharing}
                      class="flex flex-row gap-2"
                    >
                      {#if isSharing}
                        <Icon
                          icon="eos-icons:three-dots-loading"
                          class="h-4 w-4"
                        />
                      {:else}
                        <Icon icon="ph:share-network-fill" class="h-4 w-4" />
                      {/if}
                      Share
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent align="end" sideOffset={8}>
                    <ShareCollectionPopover
                      loading={isSharing}
                      close={() => (sharePopoverOpen = false)}
                      confirm={handleShareConfirm}
                    />
                  </PopoverContent>
                </Popover>
              {/if}
            </div>
          {/if}
        </div>
      {/if}
    </div>

    {#if itemsFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={8} />
      </div>
    {:else if itemsFeed.hasError}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:warning-circle-duotone"
          title="Collection not found"
          description="This collection doesn't exist or you don't have permission to view it."
          variant="red"
          size="md"
        />
      </div>
    {:else if itemsFeed.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:images-duotone"
          title="No items yet"
          description="Add images to this collection from the image viewer."
          variant="purple"
          size="md"
        />
      </div>
    {:else if itemsFeed.items.length > 0}
      <Masonry items={itemsFeed.items} class="gap-4">
        {#snippet itemTemplate(item)}
          {#if item.image}
            {@const image = item.image}
            <div
              in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
              class="group break-inside-avoid overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 hover:shadow-md dark:hover:shadow-gray-900/50 cursor-pointer"
              onclick={() => openPostViewer(image.id)}
              onkeydown={(e) => e.key === 'Enter' && openPostViewer(image.id)}
              role="button"
              tabindex="0"
            >
              <div class="relative">
                <ImagePlaceholder
                  uniqueId={image.id}
                  src={`/image/${image.attributes.fileName}`}
                  metadata={image.metadata}
                  showMetadata={false}
                  showOpenInNewTab={false}
                  rounded={false}
                />

                <div
                  class="absolute inset-0 bg-linear-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                ></div>

                <div
                  class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                >
                  <div
                    class="w-14 h-14 rounded-full bg-black/50 flex items-center justify-center"
                  >
                    <Icon icon="ph:arrows-out" class="w-7 h-7 text-white" />
                  </div>
                </div>

                <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
                  <ViewCountBadge
                    count={image.attributes.views}
                    variant="overlay"
                  />
                  <DimensionsBadge
                    width={image.metadata.width}
                    height={image.metadata.height}
                    variant="overlay"
                  />
                </div>

                <div
                  class="absolute top-2 right-2 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                >
                  <StopPropagation>
                    <DownloadButton
                      imageUrl={`/image/${image.attributes.fileName}`}
                      fileName={image.attributes.fileName}
                      size="sm"
                      variant="overlay"
                    />
                  </StopPropagation>
                  <StopPropagation>
                    <BookmarkButton
                      imageId={image.id}
                      imageOwnerId={image.owner.id}
                      isBookmarked={image.isBookmarked}
                      bookmarkCount={image.bookmarkCount}
                      size="sm"
                      variant="overlay"
                    />
                  </StopPropagation>
                </div>
              </div>

              <div class="p-3">
                <div class="flex items-center gap-2.5">
                  <UserAvatar size="sm" user={image.owner} />
                  <div class="flex-1 min-w-0">
                    <p
                      class="font-medium text-gray-900 dark:text-gray-100 text-sm leading-tight truncate"
                    >
                      {image.owner.displayName}
                    </p>
                    <div
                      class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                    >
                      <FormattedDate
                        date={image.attributes.createdAt.timestamp}
                      />
                    </div>
                  </div>
                  {#if isOwner}
                    <CollectionItemDropdown
                      loading={removingItems.has(item.itemId)}
                      onRemove={() => handleRemoveItem(item)}
                    />
                  {/if}
                </div>

                {#if image.attributes.description?.trim()}
                  <p
                    class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
                  >
                    <ExpandableText
                      maxLines={2}
                      text={image.attributes.description}
                    />
                  </p>
                {/if}
              </div>
            </div>
          {/if}
        {/snippet}
      </Masonry>

      {#if itemsFeed.hasMore}
        <div class="flex justify-center mt-12">
          <LoadMoreButton
            visible={itemsFeed.hasMore}
            loading={itemsFeed.isLoading}
            onclick={() => itemsFeed.nextPage({ debounce: 300 })}
            variant="modern"
            rounded="full"
          >
            {#snippet text()}
              <span>Load More Images</span>
            {/snippet}
          </LoadMoreButton>
        </div>
      {/if}
    {/if}
  </div>
</main>

<PostViewer />
