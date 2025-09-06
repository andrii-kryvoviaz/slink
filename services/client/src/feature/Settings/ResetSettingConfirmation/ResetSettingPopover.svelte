<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    close: () => void;
    confirm: () => void;
    name: string;
    displayValue: string;
    currentValue?: string;
  }

  let { close, confirm, name, displayValue, currentValue }: Props = $props();

  const handleConfirm = () => {
    confirm();
  };

  const handleCancel = () => {
    close();
  };
</script>

<div class="w-72 max-w-[calc(100vw-2rem)] space-y-4 p-1">
  <div class="flex items-center gap-3">
    <div
      class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-700/50"
    >
      <Icon
        icon="lucide:rotate-cw"
        class="h-4 w-4 text-blue-600 dark:text-blue-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Reset Setting
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        Restore default value
      </p>
    </div>
  </div>

  <div class="space-y-3">
    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
      {#if currentValue && currentValue !== displayValue}
        Reset <span class="font-medium text-gray-900 dark:text-white"
          >"{name}"</span
        > from current value to default?
      {:else}
        Reset <span class="font-medium text-gray-900 dark:text-white"
          >"{name}"</span
        > to its default value?
      {/if}
    </p>

    <div class="flex items-center justify-center">
      <div
        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700"
      >
        {#if currentValue && currentValue !== displayValue}
          <span class="font-mono text-sm text-gray-600 dark:text-gray-400">
            {currentValue}
          </span>
          <Icon icon="lucide:arrow-right" class="h-3 w-3 text-gray-400" />
        {/if}
        <span
          class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100"
        >
          {displayValue}
        </span>
      </div>
    </div>
  </div>

  <div class="flex gap-2 pt-2">
    <button
      type="button"
      class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500/20 active:scale-[0.98]"
      onclick={handleCancel}
    >
      Cancel
    </button>
    <button
      type="button"
      class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-sm hover:shadow-md active:scale-[0.98]"
      onclick={handleConfirm}
    >
      <Icon icon="lucide:rotate-cw" class="w-3.5 h-3.5 mr-1.5" />
      Reset
    </button>
  </div>
</div>
