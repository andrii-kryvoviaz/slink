<script lang="ts">
  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  import { Button } from '@slink/components/UI/Action';
  import { Toggle } from '@slink/components/UI/Form';
  import { Loader } from '@slink/components/UI/Loader';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  type ConfirmAction = {
    preserveOnDiskAfterDeletion: boolean;
  };

  interface Props {
    image: { id: string };
    loading?: Readable<boolean>;
    close: () => void;
    confirm: ({ preserveOnDiskAfterDeletion }: ConfirmAction) => void;
  }

  let { image, loading = readable(false), close, confirm }: Props = $props();

  let preserveOnDiskAfterDeletion: boolean = $state(false);

  const handleConfirm = () => {
    confirm({ preserveOnDiskAfterDeletion });
  };

  const handleCancel = () => {
    close();
  };
</script>

<div
  class="w-80 sm:w-80 max-sm:w-[calc(100vw-2rem)] max-sm:max-w-xs space-y-4 relative"
>
  {#if $loading}
    <div class="absolute top-0 right-0 z-10">
      <Loader variant="minimal" size="xs" />
    </div>
  {/if}

  <div
    class="flex items-center gap-3 max-sm:flex-col max-sm:items-center max-sm:gap-2 max-sm:text-center"
  >
    <div
      class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30"
    >
      <Icon
        icon="heroicons:trash"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div class="max-sm:text-center">
      <h3 class="font-semibold text-gray-900 dark:text-white">Delete Image</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  <div class="space-y-2">
    <p class="text-sm text-gray-600 dark:text-gray-400 max-sm:text-center">
      You are about to delete:
    </p>
    <div class="flex items-center max-sm:justify-center">
      <div
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50/80 dark:bg-red-950/30 border border-red-200/40 dark:border-red-800/40 text-red-700 dark:text-red-300 max-w-full"
      >
        <a
          href={`/info/${image.id}`}
          class="font-mono text-sm font-medium decoration-dashed underline transition-all duration-200 opacity-80 hover:opacity-100 underline-offset-2 truncate min-w-0"
        >
          {image.id}
        </a>
      </div>
    </div>
  </div>

  <div
    class="rounded-xl border border-gray-200/60 dark:border-gray-700/50 p-3 bg-gray-50/50 dark:bg-gray-800/50"
  >
    <div class="flex items-start gap-3">
      <Toggle
        size="sm"
        checked={!preserveOnDiskAfterDeletion}
        on={{
          change: (checked) => (preserveOnDiskAfterDeletion = !checked),
        }}
      />

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <div class="font-medium text-gray-900 dark:text-white text-sm">
            {preserveOnDiskAfterDeletion
              ? 'Preserve file on disk'
              : 'Remove from storage'}
          </div>

          <Tooltip size="sm" side="top" sideOffset={8}>
            {#snippet trigger()}
              <div
                class="flex h-4 w-4 items-center justify-center rounded-full bg-gray-200/80 dark:bg-gray-700/80 text-gray-500 dark:text-gray-400 hover:bg-gray-300/80 dark:hover:bg-gray-600/80 transition-all duration-200 cursor-help"
              >
                <Icon icon="heroicons:question-mark-circle" class="h-3 w-3" />
              </div>
            {/snippet}
            <div class="text-xs font-medium max-w-48">
              Toggle to control whether the physical file is deleted from
              storage or just the database entry is removed
            </div>
          </Tooltip>
        </div>

        <p
          class="mt-1 text-xs text-gray-600 dark:text-gray-400 leading-relaxed"
        >
          {preserveOnDiskAfterDeletion
            ? 'Keep the physical file but remove database entry'
            : 'Permanently delete the file from storage'}
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-4 max-sm:flex-col max-sm:gap-3">
    <Button
      variant="glass"
      size="md"
      onclick={handleCancel}
      class="flex-1 max-sm:w-full"
      disabled={$loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      size="md"
      class="flex-1 max-sm:w-full font-medium h-11 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
      onclick={handleConfirm}
      disabled={$loading}
    >
      <Icon icon="heroicons:trash" class="w-4 h-4 mr-2" />
      Delete Image
      {#if $loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 ml-2" />
      {/if}
    </Button>
  </div>
</div>
