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
      <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
        Changing <strong class="text-gray-900 dark:text-white font-semibold"
          >{name}</strong
        > to:
      </p>
      <div
        class="mt-4 rounded-2xl bg-gradient-to-br from-gray-50/80 to-white/60 dark:from-gray-800/60 dark:to-gray-900/40 border border-gray-200/60 dark:border-gray-700/50 px-6 py-4 backdrop-blur-sm shadow-sm"
      >
        <span
          class="font-mono text-base text-gray-900 dark:text-white font-semibold"
        >
          {displayValue}
        </span>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
