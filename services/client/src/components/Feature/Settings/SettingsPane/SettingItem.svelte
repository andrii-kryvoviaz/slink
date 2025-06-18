<script lang="ts">
  import type { Snippet } from 'svelte';

  import {
    parseFileSize,
    sizeMatchingRegex,
  } from '@slink/utils/string/parseFileSize';
  import { randomId } from '@slink/utils/string/randomId';
  import { toast } from '@slink/utils/ui/toast';

  import { ResetSettingConfirmation } from '@slink/components/Feature/Settings';
  import { Badge } from '@slink/components/UI/Text';

  interface Props {
    defaultValue?: any;
    reset?: (value: any) => void;
    label?: Snippet;
    hint?: Snippet;
    children?: Snippet;
  }

  let {
    defaultValue = null,
    reset = () => {},
    label,
    hint,
    children,
  }: Props = $props();

  const uniqueId = randomId('setting-item');

  let labelRef: HTMLSpanElement | undefined = $state();
  let triggerRef: HTMLButtonElement | undefined = $state();

  const formatDefaultValue = () => {
    if (typeof defaultValue === 'boolean') {
      return defaultValue ? 'Enabled' : 'Disabled';
    }

    if (
      typeof defaultValue === 'string' &&
      defaultValue.match(sizeMatchingRegex)
    ) {
      const { size, unit } = parseFileSize(defaultValue);

      return `${size} ${unit}`;
    }

    return defaultValue;
  };

  const handleSettingReset = () => {
    toast.component(ResetSettingConfirmation, {
      id: uniqueId,
      props: {
        name: labelRef?.innerText || '',
        displayValue,
        close: () => toast.remove(uniqueId),
        confirm: () => {
          toast.remove(uniqueId);
          reset(defaultValue);

          triggerRef?.click();
        },
      },
    });
  };

  let displayValue = $derived(formatDefaultValue());
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

          {#if displayValue}
            <button
              type="button"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100/60 dark:bg-gray-800/60 border border-gray-200/60 dark:border-gray-700/60 rounded-lg hover:bg-gray-200/60 dark:hover:bg-gray-700/60 hover:border-gray-300/60 dark:hover:border-gray-600/60 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-1"
              onclick={handleSettingReset}
              onkeydown={handleSettingReset}
              tabindex="0"
            >
              <span class="text-gray-500 dark:text-gray-400">Default:</span>
              <Badge
                variant="default"
                size="sm"
                class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700"
                >{displayValue}</Badge
              >
            </button>
          {/if}
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
