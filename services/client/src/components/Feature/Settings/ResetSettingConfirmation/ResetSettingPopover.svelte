<script lang="ts">
  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';

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
  class="w-80 sm:w-80 max-sm:w-[calc(100vw-2rem)] max-sm:max-w-xs space-y-4 relative"
>
  <div
    class="flex items-center gap-3 max-sm:flex-col max-sm:items-center max-sm:gap-2 max-sm:text-center"
  >
    <div
      class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"
    >
      <Icon
        icon="heroicons:arrow-path"
        class="h-5 w-5 text-blue-600 dark:text-blue-400"
      />
    </div>
    <div class="max-sm:text-center">
      <h3 class="font-semibold text-gray-900 dark:text-white">Reset Setting</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Restore to default value
      </p>
    </div>
  </div>

  <div class="space-y-2">
    <p class="text-sm text-gray-600 dark:text-gray-400 max-sm:text-center">
      Reset <span class="font-medium text-gray-900 dark:text-white">{name}</span
      > to default value?
    </p>
    <div class="flex items-center max-sm:justify-center">
      <div
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/40 dark:border-blue-800/40 text-blue-700 dark:text-blue-300 max-w-full"
      >
        <span class="font-mono text-sm font-medium truncate min-w-0">
          {displayValue}
        </span>
      </div>
    </div>
  </div>

  <div class="flex gap-4 max-sm:flex-col max-sm:gap-3">
    <Button
      variant="glass"
      size="md"
      onclick={handleCancel}
      class="flex-1 max-sm:w-full"
    >
      Cancel
    </Button>
    <Button
      variant="primary"
      size="md"
      class="flex-1 max-sm:w-full font-medium h-11 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
      onclick={handleConfirm}
    >
      <Icon icon="heroicons:arrow-path" class="w-4 h-4 mr-2" />
      Reset
    </Button>
  </div>
</div>
