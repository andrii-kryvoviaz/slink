<script lang="ts">
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import {
    FORMAT_OPTIONS,
    type ImageOutputFormat,
    isSameFormat,
  } from './FormatPicker.types';

  interface Props {
    value: ImageOutputFormat;
    originalFormat?: string;
    isAnimated?: boolean;
    on?: {
      change: (format: ImageOutputFormat) => void;
    };
  }

  let { value, originalFormat = '', isAnimated = false, on }: Props = $props();

  const options = $derived.by(() => {
    return FORMAT_OPTIONS.filter((opt) => {
      if (opt.value === 'original') return true;
      if (!originalFormat) return true;
      return !isSameFormat(opt.extension, originalFormat);
    }).map(
      (opt): ToggleGroupOption<ImageOutputFormat> => ({
        value: opt.value,
        label:
          opt.value === 'original' && originalFormat
            ? `${opt.label} (${originalFormat.toUpperCase()})`
            : opt.label,
      }),
    );
  });

  const showAnimationWarning = $derived(isAnimated && value !== 'original');

  const handleValueChange = (format: ImageOutputFormat) => {
    on?.change(format);
  };
</script>

<div class="space-y-3 w-fit">
  <ToggleGroup
    {value}
    {options}
    onValueChange={handleValueChange}
    size="sm"
    aria-label="Image format selection"
  />

  {#if showAnimationWarning}
    <div
      class="flex items-center gap-2 rounded-md bg-amber-500/10 px-3 py-2 text-xs text-amber-600 dark:text-amber-400"
    >
      <Icon icon="ph:warning" class="h-4 w-4 shrink-0" />
      <span>Converting to {value.toUpperCase()} will remove animation</span>
    </div>
  {/if}
</div>
