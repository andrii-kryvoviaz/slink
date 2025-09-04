<script lang="ts">
  import { Button } from '@slink/legacy/UI/Action';

  import Icon from '@iconify/svelte';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
  }

  let { close, confirm, name, displayValue }: Props = $props();

  const handleConfirm = () => {
    confirm();
  };

  const handleCancel = () => {
    close();
  };
</script>

<div
  class="w-80 sm:w-80 max-sm:w-[calc(100vw-2rem)] max-sm:max-w-xs space-y-5 relative"
>
  <div class="space-y-4">
    <div class="flex items-center gap-3">
      <div
        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-950/30 dark:to-red-900/20 border border-red-200/50 dark:border-red-800/30"
      >
        <Icon
          icon="heroicons:arrow-path"
          class="h-5 w-5 text-red-600 dark:text-red-400"
        />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Reset Setting
        </h3>
        <p class="text-sm text-red-600 dark:text-red-400">
          This will restore the default value
        </p>
      </div>
    </div>

    <div class="space-y-3">
      <div
        class="p-4 rounded-xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200/60 dark:border-gray-700/50"
      >
        <div class="space-y-3">
          <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
            Reset setting <span
              class="font-semibold text-gray-900 dark:text-white">"{name}"</span
            > to its default value?
          </p>
          <div
            class="p-3 rounded-lg bg-white dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700"
          >
            <code
              class="font-mono text-sm text-gray-900 dark:text-gray-100 break-all leading-relaxed"
            >
              {displayValue}
            </code>
          </div>
        </div>
      </div>

      <p
        class="text-sm text-red-600 dark:text-red-400 bg-red-50/50 dark:bg-red-950/20 border border-red-200/50 dark:border-red-800/30 rounded-lg px-3 py-2"
      >
        This action cannot be undone. Your current setting will be lost.
      </p>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button variant="glass" size="md" onclick={handleCancel} class="flex-1">
      Keep Current
    </Button>
    <Button
      variant="danger"
      size="md"
      class="flex-1 font-medium"
      onclick={handleConfirm}
    >
      <Icon icon="heroicons:arrow-path" class="w-4 h-4 mr-2" />
      Reset to Default
    </Button>
  </div>
</div>
