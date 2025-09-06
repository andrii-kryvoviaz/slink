<script lang="ts">
  import ResetSettingPopover from '@slink/feature/Settings/ResetSettingConfirmation/ResetSettingPopover.svelte';
  import { Badge } from '@slink/feature/Text';
  import { Overlay } from '@slink/ui/components/popover';
  import { Tooltip } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import {
    parseFileSize,
    sizeMatchingRegex,
  } from '$lib/utils/string/parseFileSize';
  import { randomId } from '$lib/utils/string/randomId';
  import Icon from '@iconify/svelte';

  interface Props {
    defaultValue?: any;
    currentValue?: any;
    reset?: (value: any) => void;
    label?: Snippet;
    hint?: Snippet;
    children?: Snippet;
  }

  let {
    defaultValue = null,
    currentValue = null,
    reset = () => {},
    label,
    hint,
    children,
  }: Props = $props();

  const uniqueId = randomId('setting-item');

  let labelRef: HTMLSpanElement | undefined = $state();
  let triggerRef: HTMLButtonElement | undefined = $state();
  let resetPopoverOpen = $state(false);

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

  const formatDefaultValue = () => {
    return formatValue(defaultValue);
  };

  const formatCurrentValue = () => {
    return formatValue(currentValue);
  };

  const handleSettingReset = () => {
    resetPopoverOpen = true;
  };

  const confirmSettingReset = () => {
    resetPopoverOpen = false;
    reset(defaultValue);
    triggerRef?.click();
  };

  const closeResetPopover = () => {
    resetPopoverOpen = false;
  };

  let displayValue = $derived(formatDefaultValue());
  let displayCurrentValue = $derived(formatCurrentValue());
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
  <div class="space-y-3">
    {#if label}
      <div class="space-y-2">
        <div class="flex items-start gap-3 flex-wrap">
          <h3
            bind:this={labelRef}
            class="text-base font-medium text-gray-900 dark:text-white leading-tight"
          >
            {@render label?.()}
          </h3>

          <div
            class="flex items-center justify-center w-6 h-6"
            class:opacity-0={!shouldShowResetButton}
            class:pointer-events-none={!shouldShowResetButton}
          >
            <Tooltip side="top" size="xs">
              {#snippet trigger()}
                <Overlay
                  bind:open={resetPopoverOpen}
                  variant="floating"
                  contentProps={{ align: 'end' }}
                >
                  {#snippet trigger()}
                    <button
                      type="button"
                      class="inline-flex items-center justify-center w-6 h-6 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-1 group"
                      aria-label="Reset to default value"
                      disabled={!shouldShowResetButton}
                    >
                      <Icon
                        icon="lucide:rotate-cw"
                        class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-12"
                      />
                    </button>
                  {/snippet}

                  <ResetSettingPopover
                    name={labelRef?.innerText || ''}
                    {displayValue}
                    currentValue={displayCurrentValue}
                    close={closeResetPopover}
                    confirm={confirmSettingReset}
                  />
                </Overlay>
              {/snippet}

              Reset to default
            </Tooltip>
          </div>
        </div>

        {#if hint}
          <p
            class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-md"
          >
            {@render hint?.()}
          </p>
        {/if}
      </div>
    {/if}
  </div>

  <div class="flex justify-start lg:justify-end">
    <div class="w-full lg:w-auto lg:min-w-[240px]">
      {#if children}
        {@render children()}
      {:else}
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
          Control not available
        </div>
      {/if}
    </div>
  </div>
</div>
