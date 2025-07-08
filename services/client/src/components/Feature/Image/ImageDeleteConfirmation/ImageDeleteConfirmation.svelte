<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { ImageListingItem } from '@slink/api/Response';

  import { Button } from '@slink/components/UI/Action';
  import { Toggle } from '@slink/components/UI/Form';

  interface Props {
    image: ImageListingItem;
    loading: boolean;
    preserveOnDisk: boolean;
    onConfirm: () => void;
    onCancel: () => void;
    onPreserveOnDiskChange: (checked: boolean) => void;
  }

  let {
    image,
    loading,
    preserveOnDisk,
    onConfirm,
    onCancel,
    onPreserveOnDiskChange,
  }: Props = $props();
</script>

<div class="w-full max-w-sm p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm flex-shrink-0"
    >
      <Icon
        icon="heroicons:trash"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Delete Image
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  <div
    class="bg-gray-50/80 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/30"
  >
    <label class="flex items-center gap-3 justify-between cursor-pointer">
      <div class="flex items-center gap-3">
        <div
          class="flex h-8 w-8 items-center justify-center rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm"
        >
          <Icon
            icon="heroicons:archive-box"
            class="h-4 w-4 text-gray-600 dark:text-gray-400"
          />
        </div>
        <div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">
            Remove from storage
          </span>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Permanently delete the file from storage
          </p>
        </div>
      </div>
      <Toggle
        size="sm"
        checked={!preserveOnDisk}
        on={{ change: (checked) => onPreserveOnDiskChange(!checked) }}
        disabled={loading}
      />
    </label>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      size="md"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      size="md"
      onclick={onConfirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={loading}
    >
      {#if loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
      {:else}
        <Icon icon="heroicons:trash" class="h-4 w-4 mr-2" />
      {/if}
      Delete Image
    </Button>
  </div>
</div>
