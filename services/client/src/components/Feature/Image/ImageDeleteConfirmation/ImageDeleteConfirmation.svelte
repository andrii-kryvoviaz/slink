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
    <div class="space-y-4">
      <div class="space-y-3">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          You are about to delete the following image:
        </p>

        <div class="flex items-center">
          <div
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50/80 dark:bg-red-950/30 border border-red-200/40 dark:border-red-800/40 text-red-700 dark:text-red-300"
          >
            <a
              href={`/info/${image.id}`}
              class="font-mono text-sm font-medium decoration-dashed underline p-2 transition-all duration-200 opacity-80 hover:opacity-100 underline-offset-2"
            >
              {image.id}
            </a>
          </div>
        </div>
      </div>

      <div
        class="rounded-xl border border-gray-200/60 dark:border-gray-700/50 p-4 bg-gray-50/50 dark:bg-gray-800/50"
      >
        <div class="flex items-start gap-3">
          <Toggle
            size="md"
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
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-200/80 dark:bg-gray-700/80 text-gray-500 dark:text-gray-400 hover:bg-gray-300/80 dark:hover:bg-gray-600/80 transition-all duration-200 cursor-help"
                  >
                    <Icon
                      icon="heroicons:question-mark-circle"
                      class="h-3 w-3"
                    />
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
    </div>
  {/snippet}
</ConfirmationDialog>
