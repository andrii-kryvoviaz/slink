<script lang="ts">
  import ConfirmationDialog from '@slink/components/UI/Modal/ConfirmationDialog.svelte';

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
  confirmText="Reset & Save"
  {close}
  {confirm}
>
  {#snippet content()}
    <div class="text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Changing <strong class="text-gray-900 dark:text-white">{name}</strong> to:
      </p>
      <div
        class="mt-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 px-4 py-3"
      >
        <span
          class="font-mono text-sm text-gray-900 dark:text-white font-medium"
        >
          {displayValue}
        </span>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
