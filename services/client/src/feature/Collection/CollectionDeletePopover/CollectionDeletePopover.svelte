<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collection: CollectionResponse;
    loading: boolean;
    deleteImages: boolean;
    onConfirm: () => void;
    onCancel: () => void;
    onDeleteImagesChange: (checked: boolean) => void;
  }

  let {
    collection,
    loading,
    deleteImages,
    onConfirm,
    onCancel,
    onDeleteImagesChange,
  }: Props = $props();

  const hasImages = $derived((collection.itemCount ?? 0) > 0);
</script>

<div class="w-full max-w-sm p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm shrink-0"
    >
      <Icon
        icon="ph:folder-simple"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Delete Collection
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  {#if hasImages}
    <div
      class="bg-gray-50/80 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/30"
    >
      <label class="flex items-center gap-3 justify-between cursor-pointer">
        <div class="flex items-center gap-3">
          <div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">
              Also delete images
            </span>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Delete all {collection.itemCount} images in this collection
            </p>
          </div>
        </div>
        <Switch
          checked={deleteImages}
          onCheckedChange={onDeleteImagesChange}
          disabled={loading}
        />
      </label>
    </div>
  {/if}

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={onConfirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={loading}
    >
      {#if loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
      {:else}
        <Icon icon="heroicons:trash" class="h-4 w-4 mr-2" />
      {/if}
      Delete
    </Button>
  </div>
</div>
