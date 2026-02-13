<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Tooltip } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import {
    parseFileSize,
    sizeMatchingRegex,
  } from '$lib/utils/string/parseFileSize';
  import Icon from '@iconify/svelte';

  interface Props {
    defaultValue?: any;
    currentValue?: any;
    reset?: (value: any) => void;
    label?: Snippet;
    hint?: Snippet;
    header?: Snippet;
    footer?: Snippet;
    children?: Snippet;
  }

  let {
    defaultValue = null,
    currentValue = null,
    reset = () => {},
    label,
    hint,
    header,
    footer,
    children,
  }: Props = $props();

  let triggerRef: HTMLButtonElement | undefined = $state();
  let showConfirm = $state(false);

  const formatValue = (value: any) => {
    if (typeof value === 'boolean') {
      return value ? 'Enabled' : 'Disabled';
    }

    if (typeof value === 'string' && value.match(sizeMatchingRegex)) {
      const { size, unit } = parseFileSize(value);
      return `${size} ${unit}`;
    }

    return value;
  };

  const confirmReset = () => {
    showConfirm = false;
    reset(defaultValue);
    triggerRef?.click();
  };

  const cancelReset = () => {
    showConfirm = false;
  };

  let displayValue = $derived(formatValue(defaultValue));
  let shouldShowResetButton = $derived(
    displayValue && currentValue !== null && currentValue !== defaultValue,
  );
</script>

<button
  bind:this={triggerRef}
  class="hidden"
  type="submit"
  aria-hidden="true"
  aria-label="Reset Setting"
></button>

<div
  class="relative flex flex-col hover:bg-gray-100/50 dark:hover:bg-gray-800/30 transition-colors duration-150 overflow-hidden"
>
  {#if header}
    {@render header?.()}
  {/if}

  <div
    class="flex items-center justify-between gap-4 sm:gap-6 px-4 py-4 flex-1 min-w-0"
  >
    <div class="flex-1 min-w-0">
      {#if label}
        <div class="flex items-center gap-2">
          <h3 class="text-sm font-medium text-gray-900 dark:text-white">
            {@render label?.()}
          </h3>

          <div
            class="flex items-center w-5 h-5 transition-opacity duration-150"
            class:opacity-0={!shouldShowResetButton || showConfirm}
            class:pointer-events-none={!shouldShowResetButton || showConfirm}
          >
            <Tooltip side="top" size="xs">
              {#snippet trigger()}
                <button
                  type="button"
                  onclick={() => (showConfirm = true)}
                  class="inline-flex items-center justify-center w-5 h-5 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200/50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                  aria-label="Reset to default value"
                >
                  <Icon icon="lucide:rotate-ccw" class="w-3 h-3" />
                </button>
              {/snippet}
              Reset to {displayValue}
            </Tooltip>
          </div>
        </div>

        {#if hint}
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {@render hint?.()}
          </p>
        {/if}
      {/if}
    </div>

    <div class="shrink-0">
      {#if children}
        {@render children()}
      {/if}
    </div>
  </div>

  {#if footer}
    {@render footer?.()}
  {/if}

  {#if showConfirm}
    <div
      class="absolute inset-0 flex items-center justify-between gap-3 px-4 bg-white dark:bg-gray-900 animate-in fade-in duration-100 z-10"
    >
      <div
        class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
      >
        <Icon icon="lucide:rotate-ccw" class="w-4 h-4 text-gray-400" />
        <span>
          Reset to <span class="font-medium text-gray-900 dark:text-white"
            >{displayValue}</span
          >
        </span>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="ghost" size="xs" rounded="lg" onclick={cancelReset}>
          Cancel
        </Button>
        <Button
          variant="soft-green"
          size="xs"
          rounded="lg"
          onclick={confirmReset}
        >
          <Icon icon="lucide:check" class="w-3.5 h-3.5 mr-1" />
          Confirm
        </Button>
      </div>
    </div>
  {/if}
</div>
