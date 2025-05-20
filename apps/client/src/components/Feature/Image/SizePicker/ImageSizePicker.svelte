<script lang="ts">
  import type { ImageSize } from '@slink/components/Feature/Image';

  import Icon from '@iconify/svelte';

  import { Button } from '@slink/components/UI/Action';
  import { Tooltip } from '@slink/components/UI/Tooltip';

  interface Props {
    width: number;
    height: number;
    on?: {
      change: (size: Partial<ImageSize>) => void;
    };
  }

  let { width, height, on }: Props = $props();

  let calculatedWidth: number = $state(width);
  let calculatedHeight: number = $state(height);

  let aspectRatioLinked = $state(true);
  let aspectRatio = $derived(width && height ? width / height : 0);

  const adjustBoundaries = () => {
    if (calculatedWidth < 0) {
      calculatedWidth = 0;
    }

    if (calculatedWidth > width) {
      calculatedWidth = width;
    }

    if (calculatedHeight < 0) {
      calculatedHeight = 0;
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
    const sizeChange: Partial<ImageSize> = {};

    if (calculatedWidth && calculatedWidth !== width) {
      sizeChange.width = calculatedWidth;
    }

    if (calculatedHeight && calculatedHeight !== height) {
      sizeChange.height = calculatedHeight;
    }

    if (Object.keys(sizeChange).length > 0) {
      on?.change(sizeChange);

      return;
    }

    on?.change({ width, height });
  };

  const handleChange = (caller: 'width' | 'height') => {
    adjustBoundaries();

    if (aspectRatioLinked) {
      if (caller === 'width') {
        calculatedHeight = Math.floor(
          Math.max(calculatedWidth, 10) / aspectRatio,
        );
      }
      if (caller === 'height') {
        calculatedWidth = Math.floor(
          Math.max(calculatedHeight, 10) * aspectRatio,
        );
      }
    }

    handleSubmit();
  };
</script>

<div class="flex gap-4 text-[0.7rem] xs:text-xs">
  <div class="flex gap-2 rounded-full border border-bc-button-default p-1">
    <input
      type="number"
      class="w-16 bg-transparent text-center focus:outline-hidden"
      bind:value={calculatedWidth}
      onkeyup={() => handleChange('width')}
      onchange={() => handleChange('width')}
    />
    <div class="flex">
      <Tooltip size="xs" side="bottom">
        {#snippet trigger()}
          <Button
            size="xs"
            variant="invisible"
            rounded="full"
            class="min-w-[3rem]"
            onclick={toggleAspectRatioLink}
          >
            {#if aspectRatioLinked}
              <Icon icon="carbon:link" />
            {:else}
              <Icon icon="carbon:unlink" />
            {/if}
          </Button>
        {/snippet}
        {#if aspectRatioLinked}
          <span>Unlink aspect ratio</span>
        {:else}
          <span>Link to aspect ratio</span>
        {/if}
      </Tooltip>
    </div>

    <input
      type="number"
      class="w-16 bg-transparent text-center focus:outline-hidden"
      bind:value={calculatedHeight}
      onkeyup={() => handleChange('height')}
      onchange={() => handleChange('height')}
    />
    <Button
      rounded="full"
      variant="danger"
      size="xs"
      class="px-4 text-[0.7rem] xs:text-xs"
      onclick={resetValues}>Reset</Button
    >
  </div>
</div>
