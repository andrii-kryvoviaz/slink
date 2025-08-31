<script lang="ts">
  import { ImageDeleteConfirmation } from '@slink/feature/Image';
  import {
    Dropdown,
    DropdownGroup,
    DropdownItem,
  } from '@slink/legacy/UI/Action';

  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    image: ImageListingItem;
    on?: {
      imageUpdate?: (updatedImage: ImageListingItem) => void;
      imageDelete?: (imageId: string) => void;
    };
  }

  let { image = $bindable(), on }: Props = $props();

  let deleteConfirmOpen = $state(false);
  let preserveOnDiskAfterDeletion = $state(false);

  const isGuestImage = $derived(image.owner.id === 'guest');

  const {
    isLoading: visibilityIsLoading,
    error: updateVisibilityError,
    run: updateVisibility,
  } = ReactiveState(
    (imageId: string, isPublic: boolean) => {
      return ApiClient.image.adminUpdateDetails(imageId, {
        isPublic,
      });
    },
    { minExecutionTime: 300 },
  );

  const {
    isLoading: deleteImageIsLoading,
    error: deleteImageError,
    run: deleteImage,
  } = ReactiveState((imageId: string, preserveOnDisk: boolean) => {
    return ApiClient.image.adminRemove(imageId, preserveOnDisk);
  });

  const handleVisibilityChange = async (isPublic: boolean) => {
    await updateVisibility(image.id, isPublic);

    if ($updateVisibilityError) {
      toast.error('Failed to update visibility. Please try again later.');
      return;
    }

    const updatedImage = {
      ...image,
      attributes: { ...image.attributes, isPublic },
    };

    image = updatedImage;
    on?.imageUpdate?.(updatedImage);
  };

  const handleImageDeletion = () => {
    deleteConfirmOpen = true;
  };

  const confirmImageDeletion = async () => {
    await deleteImage(image.id, preserveOnDiskAfterDeletion);

    if ($deleteImageError) {
      toast.error('Failed to delete image. Please try again later.');
      return;
    }

    deleteConfirmOpen = false;
    on?.imageDelete?.(image.id);
  };

  const cancelImageDeletion = () => {
    deleteConfirmOpen = false;
    preserveOnDiskAfterDeletion = false;
  };
</script>

<div class="relative -mr-3">
  <Dropdown variant="invisible" size="xs">
    {#snippet trigger()}
      <button
        class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
      >
        <Icon icon="heroicons:ellipsis-vertical" class="w-5 h-5" />
      </button>
    {/snippet}

    <DropdownGroup>
      {#if !deleteConfirmOpen}
        <DropdownItem
          on={{
            click: () => handleVisibilityChange(!image.attributes.isPublic),
          }}
          closeOnSelect={false}
          loading={$visibilityIsLoading}
          disabled={isGuestImage}
        >
          {#snippet icon()}
            <Icon
              icon={image.attributes.isPublic ? 'ph:eye-slash' : 'ph:eye'}
              class="h-4 w-4 {isGuestImage ? 'text-gray-400' : ''}"
            />
          {/snippet}
          <span class={isGuestImage ? 'text-gray-400' : ''}
            >{image.attributes.isPublic ? 'Make Private' : 'Make Public'}</span
          >
        </DropdownItem>
      {/if}
    </DropdownGroup>

    <DropdownGroup>
      {#if !deleteConfirmOpen}
        <DropdownItem
          danger={true}
          on={{ click: handleImageDeletion }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="heroicons:trash" class="h-4 w-4" />
          {/snippet}
          <span>Delete</span>
        </DropdownItem>
      {:else}
        <ImageDeleteConfirmation
          {image}
          loading={$deleteImageIsLoading}
          preserveOnDisk={preserveOnDiskAfterDeletion}
          onConfirm={confirmImageDeletion}
          onCancel={cancelImageDeletion}
          onPreserveOnDiskChange={(checked: boolean) =>
            (preserveOnDiskAfterDeletion = checked)}
        />
      {/if}
    </DropdownGroup>
  </Dropdown>
</div>
