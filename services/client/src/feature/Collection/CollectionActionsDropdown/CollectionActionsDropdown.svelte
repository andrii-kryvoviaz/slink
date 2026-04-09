<script lang="ts">
  import {
    CollectionDeletePopover,
    CreateCollectionForm,
  } from '@slink/feature/Collection';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';
  import { Dialog } from '@slink/ui/components/dialog';

  import { t } from '$lib/i18n';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { ValidationException } from '@slink/api/Exceptions';
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
  let editModalOpen = $state(false);
  let editFormErrors = $state<Record<string, string>>({});
  let isEditing = $state(false);

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
      toast.error($t('collection.actions.delete_failed'));
      return;
    }

    deleteConfirmOpen = false;
    deleteImages = false;
  };

  const cancelDelete = () => {
    deleteConfirmOpen = false;
    deleteImages = false;
  };

  const handleEditClick = () => {
    editModalOpen = true;
    editFormErrors = {};
  };

  const handleEditSubmit = async (data: {
    name: string;
    description?: string;
  }) => {
    try {
      isEditing = true;
      editFormErrors = {};

      await collectionsFeed.updateCollection(collection.id, data);
      editModalOpen = false;
      toast.success($t('collection.actions.updated_successfully'));
    } catch (error) {
      if (error instanceof ValidationException && error.violations) {
        editFormErrors = error.violations.reduce<Record<string, string>>(
          (acc, violation) => {
            acc[violation.property] = violation.message;
            return acc;
          },
          {},
        );
      } else {
        toast.error($t('collection.actions.update_failed'));
      }
    } finally {
      isEditing = false;
    }
  };

  const handleCloseEditModal = () => {
    editModalOpen = false;
    editFormErrors = {};
  };
</script>

<div class="relative">
  <DropdownSimple variant="invisible" size="xs">
    {#snippet trigger(triggerProps)}
      <Button
        variant="glass"
        size="icon"
        padding="none"
        rounded="md"
        {...triggerProps}
        aria-label={$t('collection.actions.aria')}
      >
        <Icon icon="lucide:ellipsis" class="h-4 w-4" />
      </Button>
    {/snippet}

    <DropdownSimpleGroup>
      {#if !deleteConfirmOpen}
        <DropdownSimpleItem on={{ click: handleEditClick }}>
          {#snippet icon()}
            <Icon icon="ph:note-pencil" class="h-4 w-4" />
          {/snippet}
          <span>{$t('collection.actions.edit')}</span>
        </DropdownSimpleItem>
        <DropdownSimpleItem
          danger={true}
          on={{ click: handleDeleteClick }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="heroicons:trash" class="h-4 w-4" />
          {/snippet}
          <span>{$t('collection.actions.delete')}</span>
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

<Dialog bind:open={editModalOpen} size="md">
  {#snippet children()}
    <CreateCollectionForm
      mode="edit"
      initialData={{
        name: collection.name,
        description: collection.description ?? '',
      }}
      isSubmitting={isEditing}
      errors={editFormErrors}
      onSubmit={handleEditSubmit}
      onCancel={handleCloseEditModal}
    />
  {/snippet}
</Dialog>
