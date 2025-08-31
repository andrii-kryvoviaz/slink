<script lang="ts">
  import ConfirmationDialog from '@slink/legacy/UI/Modal/ConfirmationDialog.svelte';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
  }

  let { close, confirm, name, displayValue }: Props = $props();
</script>

<ConfirmationDialog
  variant="info"
  icon="heroicons:arrow-path"
  title="Reset Setting"
  confirmText="Reset"
  {close}
  {confirm}
>
  {#snippet content()}
    <div class="space-y-3">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Reset <span class="font-medium text-gray-900 dark:text-white"
          >{name}</span
        > to default value?
      </p>

      <div class="flex items-center">
        <div
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/40 dark:border-blue-800/40 text-blue-700 dark:text-blue-300"
        >
          <span class="font-mono text-sm font-medium">
            {displayValue}
          </span>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
