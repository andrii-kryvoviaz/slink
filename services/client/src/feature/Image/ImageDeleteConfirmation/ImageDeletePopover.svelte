<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  type ConfirmAction = {
    preserveOnDiskAfterDeletion: boolean;
  };

  interface Props {
    loading?: Readable<boolean>;
    close: () => void;
    confirm: ({ preserveOnDiskAfterDeletion }: ConfirmAction) => void;
  }

  let { loading = readable(false), close, confirm }: Props = $props();

  let preserveOnDiskAfterDeletion: boolean = $state(false);

  const handleConfirm = () => {
    confirm({ preserveOnDiskAfterDeletion });
  };

  const handleCancel = () => {
    close();
  };
</script>

<div class="w-xs max-w-screen space-y-4 relative">
  {#if $loading}
    <div class="absolute top-2 right-2 z-10">
      <Loader variant="minimal" size="xs" />
    </div>
  {/if}

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
    <label class="flex items-center justify-between cursor-pointer">
      <div class="flex items-center gap-3">
        <div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">
            Remove from storage
          </span>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Permanently delete the file from storage
          </p>
        </div>
      </div>
      <Switch
        checked={!preserveOnDiskAfterDeletion}
        onCheckedChange={(checked) => (preserveOnDiskAfterDeletion = !checked)}
        disabled={$loading}
      />
    </label>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={handleCancel}
      class="flex-1"
      disabled={$loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={handleConfirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={$loading}
    >
      {#if $loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
      {:else}
        <Icon icon="heroicons:trash" class="h-4 w-4 mr-2" />
      {/if}
      Delete Image
    </Button>
  </div>
</div>
