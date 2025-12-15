<script lang="ts">
  import type { ImageSize } from '@slink/feature/Image';
  import { FractionPicker } from '@slink/feature/Image/FractionPicker';
  import { visibilityTheme } from '@slink/feature/Image/SizePicker/ImageSizePicker.theme';
  import { Button } from '@slink/ui/components/button';

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

  const adjustBoundaries = () => {
    if (calculatedWidth < 1) {
      calculatedWidth = 1;
    }

    if (calculatedWidth > width) {
      calculatedWidth = width;
    }

    if (calculatedHeight < 1) {
      calculatedHeight = 1;
    }

    if (calculatedHeight > height) {
      calculatedHeight = height;
    }
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
    adjustBoundaries();

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

  const handleInputFocus = (input: HTMLInputElement) => {
    input.select();
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
      <div class="relative">
        <input
          id="width-input"
          bind:this={widthInput}
          type="number"
          min="1"
          max={width}
          step="1"
          class="w-full px-3 py-2 text-sm bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200/50 dark:border-gray-700/30 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-gray-200/50 dark:focus:border-gray-700/30 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
          class:border-red-300={calculatedWidth < 1 || calculatedWidth > width}
          class:focus:border-red-500={calculatedWidth < 1 ||
            calculatedWidth > width}
          bind:value={calculatedWidth}
          onfocus={(e) =>
            e.target && handleInputFocus(e.target as HTMLInputElement)}
          onkeydown={(e) => handleKeyDown(e, 'width')}
          onkeyup={() => handleChange('width')}
          onchange={() => handleChange('width')}
          placeholder={width.toString()}
        />
      </div>
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
      <div class="relative">
        <input
          id="height-input"
          bind:this={heightInput}
          type="number"
          min="1"
          max={height}
          step="1"
          class="w-full px-3 py-2 text-sm bg-gray-50/80 dark:bg-gray-800/50 border border-gray-200/50 dark:border-gray-700/30 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-gray-200/50 dark:focus:border-gray-700/30 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200"
          class:border-red-300={calculatedHeight < 1 ||
            calculatedHeight > height}
          class:focus:border-red-500={calculatedHeight < 1 ||
            calculatedHeight > height}
          bind:value={calculatedHeight}
          onfocus={(e) =>
            e.target && handleInputFocus(e.target as HTMLInputElement)}
          onkeydown={(e) => handleKeyDown(e, 'height')}
          onkeyup={() => handleChange('height')}
          onchange={() => handleChange('height')}
          placeholder={height.toString()}
        />
      </div>
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
