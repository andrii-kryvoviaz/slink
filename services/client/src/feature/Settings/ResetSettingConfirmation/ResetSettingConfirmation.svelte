<script lang="ts">
  import { ConfirmationDialog } from '@slink/ui/components/confirmation-dialog';

  import { t } from '$lib/i18n';

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
  title={$t('settings.reset_dialog.title')}
  confirmText={$t('settings.reset_dialog.confirm')}
  cancelText={$t('settings.reset_dialog.cancel')}
  {close}
  {confirm}
>
  {#snippet content()}
    <div class="space-y-6">
      <div class="space-y-3">
        <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed">
          {$t('settings.reset_dialog.confirm_message_prefix')}
          <span class="font-semibold text-gray-900 dark:text-white"
            >"{name}"</span
          >
          {$t('settings.reset_dialog.confirm_message_suffix')}
        </p>

        <p
          class="text-sm text-amber-700 dark:text-amber-300 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/50 dark:border-amber-800/40 rounded-lg px-3 py-2"
        >
          {$t('settings.reset_dialog.warning')}
        </p>
      </div>

      <div class="space-y-2">
        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {$t('settings.reset_dialog.current_value')}
        </div>
        <div
          class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200/60 dark:border-gray-700/50"
        >
          <code
            class="font-mono text-sm text-gray-900 dark:text-gray-100 break-all"
          >
            {displayValue}
          </code>
        </div>
      </div>
    </div>
  {/snippet}
</ConfirmationDialog>
