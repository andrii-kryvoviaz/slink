<script lang="ts">
  import { CollectionDeletePopover } from '@slink/feature/Collection';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';

  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { CollectionResponse } from '@slink/api/Response';

  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';

  interface Props {
    collection: CollectionResponse;
  }

  let { collection }: Props = $props();

  const collectionsFeed = useCollectionListFeed();

  let deleteConfirmOpen = $state(false);
  let deleteImages = $state(false);

  const {
    isLoading: deleteIsLoading,
    error: deleteError,
    run: deleteCollection,
  } = ReactiveState(
    (collectionId: string, deleteImages: boolean) => {
      return collectionsFeed.deleteCollection(collectionId, deleteImages);
    },
    { minExecutionTime: 300 },
  );

  const handleDeleteClick = () => {
    deleteConfirmOpen = true;
  };

  const confirmDelete = async () => {
    await deleteCollection(collection.id, deleteImages);

    if ($deleteError) {
      toast.error('Failed to delete collection. Please try again later.');
      return;
    }

    deleteConfirmOpen = false;
    deleteImages = false;
  };

  const cancelDelete = () => {
    deleteConfirmOpen = false;
    deleteImages = false;
  };
</script>

<div class="relative">
  <DropdownSimple variant="invisible" size="xs">
    {#snippet trigger()}
      <button
        class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150"
      >
        <Icon icon="heroicons:ellipsis-vertical" class="w-4 h-4" />
      </button>
    {/snippet}

    <DropdownSimpleGroup>
      {#if !deleteConfirmOpen}
        <DropdownSimpleItem
          danger={true}
          on={{ click: handleDeleteClick }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="heroicons:trash" class="h-4 w-4" />
          {/snippet}
          <span>Delete</span>
        </DropdownSimpleItem>
      {:else}
        <CollectionDeletePopover
          {collection}
          loading={$deleteIsLoading}
          {deleteImages}
          onConfirm={confirmDelete}
          onCancel={cancelDelete}
          onDeleteImagesChange={(checked) => (deleteImages = checked)}
        />
      {/if}
    </DropdownSimpleGroup>
  </DropdownSimple>
</div>
