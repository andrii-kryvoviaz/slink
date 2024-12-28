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

<div class="flex flex-wrap items-start justify-between gap-2 sm:flex-nowrap">
  {#if label}
    <h2 class="text-md flex flex-col items-start gap-2 font-light">
      <span
        class="flex flex-col flex-wrap items-start gap-2 text-gray-800 dark:text-gray-400 md:flex-row"
      >
        <span bind:this={labelRef}>{@render label?.()}</span>

        {#if defaultValue}
          <span
            class="flex cursor-pointer select-none items-center gap-2 rounded-full bg-gray-200 p-1 px-4 text-xs font-extralight text-gray-600 transition-colors duration-200 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            onclick={handleSettingReset}
            onkeydown={handleSettingReset}
            role="button"
            tabindex="0"
          >
            <span class="font-light">Default</span>
            <Badge variant="default" size="sm">{displayValue}</Badge>
          </span>
        {/if}
      </span>

      {#if hint}
        <span class="w-64 text-xs font-extralight text-gray-500">
          {@render hint?.()}
        </span>
      {/if}
    </h2>
  {/if}

  {#if children}{@render children()}{:else}Isn't available at the moment{/if}
</div>
