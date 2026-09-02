<script lang="ts">
  import { ConfirmationDialog } from '@slink/ui/components/confirmation-dialog';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
  }

  let { close, confirm, name, displayValue }: Props = $props();
</script>

<ConfirmationDialog
  variant="amber"
  icon="heroicons:arrow-path"
  title="Reset Setting"
  confirmText="Reset to Default"
  cancelText="Keep Current"
  {close}
  {confirm}
>
  {#snippet content()}
    <div class="space-y-6">
      <div class="space-y-3">
        <p class="text-base text-foreground-soft leading-relaxed">
          Are you sure you want to reset
          <span class="font-semibold text-foreground">"{name}"</span>
          to its default value?
        </p>

        <p
          class="text-sm text-warning-text bg-warning-wash/80 dark:bg-warning-wash/16 border border-warning-border/50 dark:border-warning-border/15 rounded-lg px-3 py-2"
        >
          This action cannot be undone. Your current setting will be lost.
        </p>
      </div>

      <div class="space-y-2">
        <div class="text-sm font-medium text-foreground-soft">
          Current Value:
        </div>
        <div class="p-4 rounded-xl bg-muted-soft border border-border/60">
          <code class="font-mono text-sm text-foreground break-all">
            {displayValue}
          </code>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
