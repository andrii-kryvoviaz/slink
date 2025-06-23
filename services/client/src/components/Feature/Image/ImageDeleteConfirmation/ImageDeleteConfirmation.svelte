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
    <div class="flex flex-col items-center gap-6">
      <div
        class="rounded-2xl bg-gradient-to-br from-gray-50/80 to-white/60 dark:from-gray-800/60 dark:to-gray-900/40 border border-gray-200/60 dark:border-gray-700/50 p-6 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-200"
      >
        <div class="flex items-center justify-between">
          <div>
            <span
              class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              Image ID
            </span>
            <div class="mt-2">
              <a
                href={`/info/${image.id}`}
                class="font-mono text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors duration-200 underline decoration-dotted underline-offset-4 hover:decoration-solid font-medium"
              >
                {image.id}
              </a>
            </div>
          </div>
        </div>
      </div>

      <div
        class="rounded-2xl border border-gray-200/60 dark:border-gray-700/50 p-6 bg-gradient-to-br from-white/80 to-gray-50/60 dark:from-gray-800/60 dark:to-gray-900/40 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-200"
      >
        <div class="flex items-center justify-between gap-6">
          <div class="flex items-start gap-4">
            <Toggle
              size="md"
              checked={!preserveOnDiskAfterDeletion}
              on={{
                change: (checked) => (preserveOnDiskAfterDeletion = !checked),
              }}
            />

            <div class="flex-1">
              <div
                class="font-semibold text-gray-900 dark:text-white text-base"
              >
                {preserveOnDiskAfterDeletion
                  ? 'Preserve file on disk'
                  : 'Remove from storage'}
              </div>
              <p
                class="mt-1.5 text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
              >
                {preserveOnDiskAfterDeletion
                  ? 'Keep the physical file but remove database entry'
                  : 'Permanently delete the file from storage'}
              </p>
            </div>
          </div>

          <Tooltip size="sm" side="left" sideOffset={12}>
            {#snippet trigger()}
              <div
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100/80 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 hover:bg-gray-200/80 dark:hover:bg-gray-700/80 transition-all duration-200 cursor-help backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:shadow-sm"
              >
                <Icon icon="heroicons:question-mark-circle" class="h-4 w-4" />
              </div>
            {/snippet}
            <div class="text-xs font-medium">
              Toggle to control whether the physical file is deleted from
              storage or just the database entry is removed
            </div>
          </Tooltip>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
