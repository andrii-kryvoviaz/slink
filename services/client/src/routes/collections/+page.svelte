<script lang="ts">
  import { LoadMoreButton, StopPropagation } from '@slink/feature/Action';
  import { CollectionActionsDropdown } from '@slink/feature/Collection';
  import { CreateCollectionForm } from '@slink/feature/Collection';
  import { EmptyState } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import { FormattedDate } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { Dialog } from '@slink/ui/components/dialog';
  import { LazyImage } from '@slink/ui/components/lazy-image';

  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { ValidationException } from '@slink/api/Exceptions';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';

  const collectionsFeed = useCollectionListFeed();
  collectionsFeed.reset();

  let createModalOpen = $state(false);
  let createFormErrors = $state<Record<string, string>>({});
  let isCreating = $state(false);

  $effect(() => {
    if (!collectionsFeed.isDirty) {
      collectionsFeed.load();
    }
  });

  async function handleCreateSubmit(data: {
    name: string;
    description?: string;
  }) {
    try {
      isCreating = true;
      createFormErrors = {};

      await collectionsFeed.createCollection(data.name, data.description);
      createModalOpen = false;
    } catch (error) {
      if (error instanceof ValidationException && error.violations) {
        createFormErrors = error.violations.reduce<Record<string, string>>(
          (acc, violation) => {
            acc[violation.property] = violation.message;
            return acc;
          },
          {},
        );
      }
    } finally {
      isCreating = false;
    }
  }

  function handleCloseCreateModal() {
    createModalOpen = false;
    createFormErrors = {};
  }
</script>

<svelte:head>
  <title>Collections | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{
      feed: collectionsFeed,
      minDisplayTime: 300,
      showDelay: 100,
    }}
  >
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Collections
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">
          Organize your images into albums
        </p>
      </div>
      <Button
        variant="glass"
        size="sm"
        rounded="full"
        onclick={() => (createModalOpen = true)}
        class="gap-2"
      >
        <Icon icon="lucide:plus" class="h-4 w-4" />
        <span class="hidden sm:inline">New Collection</span>
      </Button>
    </div>

    {#if collectionsFeed.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={6} />
      </div>
    {:else if collectionsFeed.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:folder-simple-duotone"
          title="No collections yet"
          description="Create your first collection to organize and share your images."
          actionText="Create Collection"
          variant="purple"
          size="md"
          actionClick={() => (createModalOpen = true)}
        />
      </div>
    {:else if collectionsFeed.items.length > 0}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {#each collectionsFeed.items as collection (collection.id)}
          <div
            in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
            class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 hover:shadow-md dark:hover:shadow-gray-900/50"
          >
            <a href="/collection/{collection.id}" class="block">
              <div
                class="aspect-4/3 bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center relative overflow-hidden"
              >
                <LazyImage
                  src={collection.coverImage}
                  alt={collection.name}
                  class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                  containerClass="w-full h-full"
                >
                  {#snippet placeholder()}
                    <Icon
                      icon="ph:folder-simple-duotone"
                      class="w-12 h-12 text-gray-400 dark:text-gray-600 group-hover:text-gray-500 dark:group-hover:text-gray-500 transition-colors"
                    />
                  {/snippet}
                </LazyImage>
                <div class="absolute bottom-2 left-2">
                  <span
                    class="flex items-center gap-1 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
                  >
                    <Icon icon="ph:images" class="w-3.5 h-3.5" />
                    {collection.itemCount ?? 0}
                  </span>
                </div>
              </div>
            </a>
            <div class="p-3 flex items-start justify-between gap-2">
              <a href="/collection/{collection.id}" class="flex-1 min-w-0">
                <h3
                  class="font-medium text-gray-900 dark:text-white truncate text-sm"
                >
                  {@html collection.name}
                </h3>
                {#if collection.description}
                  <p
                    class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2"
                  >
                    {@html collection.description}
                  </p>
                {/if}
                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                  <FormattedDate date={collection.createdAt.timestamp} />
                </div>
              </a>
              <StopPropagation>
                <CollectionActionsDropdown {collection} />
              </StopPropagation>
            </div>
          </div>
        {/each}
      </div>

      {#if collectionsFeed.hasMore}
        <div class="flex justify-center mt-8">
          <LoadMoreButton
            visible={collectionsFeed.hasMore}
            loading={collectionsFeed.isLoading}
            onclick={() => collectionsFeed.nextPage({ debounce: 300 })}
            variant="modern"
            rounded="full"
          >
            {#snippet text()}
              <span>Load More</span>
            {/snippet}
          </LoadMoreButton>
        </div>
      {/if}
    {/if}
  </div>
</main>

<Dialog bind:open={createModalOpen} size="md">
  {#snippet children()}
    <CreateCollectionForm
      isSubmitting={isCreating}
      errors={createFormErrors}
      onSubmit={handleCreateSubmit}
      onCancel={handleCloseCreateModal}
    />
  {/snippet}
</Dialog>
