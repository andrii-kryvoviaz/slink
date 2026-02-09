<script lang="ts">
  import type { ImageSize } from '@slink/feature/Image';
  import { FractionPicker } from '@slink/feature/Image/FractionPicker';
  import { visibilityTheme } from '@slink/feature/Image/SizePicker/ImageSizePicker.theme';
  import { Button } from '@slink/ui/components/button';
  import { NumberInput } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  interface Props {
    width: number;
    height: number;
    on?: {
      change: (size: Partial<ImageSize & { crop: boolean }>) => void;
    };
  }

  let { width, height, on }: Props = $props();

  let calculatedWidth: number = $state(width);
  let calculatedHeight: number = $state(height);

  $effect(() => {
    calculatedWidth = width;
    calculatedHeight = height;
  });
  let widthInput: HTMLInputElement | undefined = $state();
  let heightInput: HTMLInputElement | undefined = $state();

  let aspectRatioLinked = $state(true);
  let aspectRatio = $derived(width && height ? width / height : 0);
  let hasChanges = $derived(
    calculatedWidth !== width || calculatedHeight !== height,
  );

  const applyFraction = (fraction: number) => {
    calculatedWidth = Math.max(1, Math.round(width * fraction));
    calculatedHeight = Math.max(1, Math.round(height * fraction));
    handleSubmit();
  };

  const resetValues = () => {
    calculatedWidth = width;
    calculatedHeight = height;
    handleSubmit();
  };

  const toggleAspectRatioLink = () => {
    aspectRatioLinked = !aspectRatioLinked;

    if (aspectRatioLinked) {
      handleChange('width');
    }
  };

  const handleSubmit = () => {
    const sizeChange: Partial<ImageSize & { crop?: boolean }> = {};

    if (calculatedWidth && calculatedWidth !== width) {
      sizeChange.width = calculatedWidth;
    }

    if (calculatedHeight && calculatedHeight !== height) {
      sizeChange.height = calculatedHeight;
    }

    if (!aspectRatioLinked) {
      sizeChange.crop = true;
    }

    on?.change(sizeChange);
  };

  const handleChange = (caller: 'width' | 'height') => {
    if (aspectRatioLinked) {
      if (caller === 'width') {
        calculatedHeight = Math.floor(
          Math.max(calculatedWidth, 1) / aspectRatio,
        );
      }
      if (caller === 'height') {
        calculatedWidth = Math.floor(
          Math.max(calculatedHeight, 1) * aspectRatio,
        );
      }
    }

    handleSubmit();
  };

  const handleKeyDown = (event: KeyboardEvent, type: 'width' | 'height') => {
    if (event.key === 'Enter') {
      event.preventDefault();
      handleChange(type);
      if (type === 'width') {
        heightInput?.focus();
      } else {
        widthInput?.focus();
      }
    }
    if (event.key === 'Escape') {
      event.preventDefault();
      resetValues();
    }
  };

  const formatValue = (value: number) => {
    return value.toLocaleString();
  };
</script>

<div class="space-y-3">
  <div class="flex items-center gap-3">
    <div class="flex-1">
      <label
        for="width-input"
        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Width
      </label>
      <NumberInput
        id="width-input"
        bind:inputRef={widthInput}
        bind:value={calculatedWidth}
        min={1}
        max={width}
        step={1}
        size="md"
        hasError={calculatedWidth < 1 || calculatedWidth > width}
        onchange={() => handleChange('width')}
        onkeydown={(e) => handleKeyDown(e, 'width')}
        placeholder={width.toString()}
      />
    </div>

    <div class="flex flex-col items-center self-end-safe">
      <Button
        variant="primary"
        onclick={toggleAspectRatioLink}
        aria-pressed={aspectRatioLinked}
      >
        {#if aspectRatioLinked}
          <Icon icon="lucide:link" class="h-4 w-4" />
        {:else}
          <Icon icon="lucide:unlink" class="h-4 w-4" />
        {/if}
      </Button>
    </div>

    <div class="flex-1">
      <label
        for="height-input"
        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Height
      </label>
      <NumberInput
        id="height-input"
        bind:inputRef={heightInput}
        bind:value={calculatedHeight}
        min={1}
        max={height}
        step={1}
        size="md"
        hasError={calculatedHeight < 1 || calculatedHeight > height}
        onchange={() => handleChange('height')}
        onkeydown={(e) => handleKeyDown(e, 'height')}
        placeholder={height.toString()}
      />
    </div>
  </div>

  <FractionPicker
    currentWidth={calculatedWidth}
    currentHeight={calculatedHeight}
    originalWidth={width}
    originalHeight={height}
    on={{ change: applyFraction }}
  />

  <div
    class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 h-6"
  >
    <div class="flex items-center gap-2">
      <span>
        Original: {formatValue(width)} × {formatValue(height)}
      </span>
      <button
        onclick={resetValues}
        class="{visibilityTheme({
          visible: hasChanges,
        })} inline-flex items-center gap-1 px-2 py-1 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
        aria-label="Reset to original dimensions"
        tabindex={hasChanges ? 0 : -1}
      >
        <Icon icon="lucide:rotate-ccw" class="h-3 w-3" />
        Reset
      </button>
    </div>
    <span class={visibilityTheme({ visible: hasChanges })}>
      New: {formatValue(calculatedWidth)} × {formatValue(calculatedHeight)}
    </span>
  </div>
</div>
