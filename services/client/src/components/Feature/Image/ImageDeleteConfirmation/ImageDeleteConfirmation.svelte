<script lang="ts">
  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  import { Toggle } from '@slink/components/UI/Form';
  import ConfirmationDialog from '@slink/components/UI/Modal/ConfirmationDialog.svelte';
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
</script>

<ConfirmationDialog
  variant="danger"
  icon="heroicons:trash"
  title="Delete Image"
  message="This action cannot be undone. The image will be permanently removed."
  confirmText="Delete Image"
  {loading}
  {close}
  confirm={handleConfirm}
>
  {#snippet content()}
    <div class="flex flex-col items-center gap-4 mb-4">
      <div
        class="rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 p-4"
      >
        <div class="flex items-center justify-between">
          <div>
            <span
              class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide"
            >
              Image ID
            </span>
            <div class="mt-1">
              <a
                href={`/info/${image.id}`}
                class="font-mono text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200 underline decoration-dotted underline-offset-2"
              >
                {image.id}
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-start gap-3">
            <Toggle
              size="md"
              checked={!preserveOnDiskAfterDeletion}
              on={{
                change: (checked) => (preserveOnDiskAfterDeletion = !checked),
              }}
            />

            <div class="flex-1">
              <div class="font-medium text-gray-900 dark:text-white">
                {preserveOnDiskAfterDeletion
                  ? 'Preserve file on disk'
                  : 'Remove from storage'}
              </div>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {preserveOnDiskAfterDeletion
                  ? 'Keep the physical file but remove database entry'
                  : 'Permanently delete the file from storage'}
              </p>
            </div>
          </div>

          <Tooltip size="sm" side="left" sideOffset={12}>
            {#snippet trigger()}
              <div
                class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 cursor-help"
              >
                <Icon icon="heroicons:question-mark-circle" class="h-4 w-4" />
              </div>
            {/snippet}
            <div class="text-xs">
              Toggle to control whether the physical file is deleted from
              storage or just the database entry is removed
            </div>
          </Tooltip>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
