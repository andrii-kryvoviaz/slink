<script lang="ts">
  import {
    parseFileSize,
    sizeMatchingRegex,
  } from '@slink/utils/string/parseFileSize';
  import { randomId } from '@slink/utils/string/randomId';
  import { toast } from '@slink/utils/ui/toast';

  import { ResetSettingConfirmation } from '@slink/components/Feature/Settings';
  import { Badge } from '@slink/components/UI/Text';

  export let defaultValue: any = null;
  export let reset: (value: any) => void = () => {};

  const uniqueId = randomId('setting-item');

  let labelRef: HTMLSpanElement;
  let triggerRef: HTMLButtonElement;

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
        name: labelRef.innerText,
        displayValue,
        close: () => toast.remove(uniqueId),
        confirm: () => {
          toast.remove(uniqueId);
          reset(defaultValue);

          triggerRef.click();
        },
      },
    });
  };

  $: displayValue = formatDefaultValue();
</script>

<button
  bind:this={triggerRef}
  class="hidden"
  type="submit"
  aria-hidden="true"
  aria-label="Reset Setting"
/>

<div class="flex flex-wrap items-center justify-between gap-2 sm:flex-nowrap">
  {#if $$slots.label}
    <h2 class="text-md flex flex-col items-start gap-2 font-light">
      <span
        class="flex flex-col flex-wrap items-start gap-2 text-gray-800 dark:text-gray-400 md:flex-row"
      >
        <span bind:this={labelRef}><slot name="label" /></span>

        {#if defaultValue !== null && defaultValue !== undefined}
          <span
            class="flex cursor-pointer select-none items-center gap-2 rounded-full bg-gray-200 p-1 px-4 text-xs font-extralight text-gray-600 transition-colors duration-200 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            on:click={handleSettingReset}
            on:keydown={handleSettingReset}
            role="button"
            tabindex="0"
          >
            <span class="font-light">Default</span>
            <Badge variant="default" size="sm">{displayValue}</Badge>
          </span>
        {/if}
      </span>

      {#if $$slots.hint}
        <span class="w-64 text-xs font-extralight text-gray-500">
          <slot name="hint" />
        </span>
      {/if}
    </h2>
  {/if}

  <slot>Isn't available at the moment</slot>
</div>
