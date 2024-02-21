<script lang="ts">
  import Icon from '@iconify/svelte';
  import { Tooltip } from '@slink/components/Common';
  import { createEventDispatcher } from 'svelte';
  import type { ImageSize } from '@slink/components/Image';
  import { Button } from '@slink/components/Common';

  export let width: number;
  export let height: number;

  const dispatch = createEventDispatcher();

  let calculatedWidth: number = 0;
  let calculatedHeight: number = 0;

  let aspectRatioLinked = true;
  let aspectRatio = 0;

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

  const initializeValues = () => {
    calculatedWidth = width;
    calculatedHeight = height;
  };

  const resetValues = () => {
    initializeValues();

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
      dispatch('change', sizeChange);

      return;
    }

    // fallback to original values
    dispatch('change');
  };

  const handleChange = (caller: 'width' | 'height') => {
    adjustBoundaries();

    if (aspectRatioLinked) {
      if (caller === 'width') {
        calculatedHeight = Math.floor(
          Math.max(calculatedWidth, 10) / aspectRatio
        );
      }
      if (caller === 'height') {
        calculatedWidth = Math.floor(
          Math.max(calculatedHeight, 10) * aspectRatio
        );
      }
    }

    handleSubmit();
  };

  $: if (width && height) {
    initializeValues();

    aspectRatio = width / height;
  }
</script>

<div class="flex gap-4 text-[0.5rem] xs:text-xs">
  <div class="flex gap-2 rounded-full border border-button-default p-1">
    <input
      type="number"
      bind:value={calculatedWidth}
      on:keyup={() => handleChange('width')}
      on:change={() => handleChange('width')}
    />
    <div class="flex">
      <Button
        size="xs"
        variant="invisible"
        rounded="full"
        class="min-w-[3rem]"
        on:click={toggleAspectRatioLink}
        id="open-link-tooltip"
      >
        {#if aspectRatioLinked}
          <Icon icon="carbon:unlink" />
        {:else}
          <Icon icon="carbon:link" />
        {/if}
      </Button>
      <Tooltip
        triggeredBy="[id^='open-link-tooltip']"
        class="p-2 text-xs"
        color="dark"
        placement="bottom"
      >
        {#if aspectRatioLinked}
          <span>Unlink aspect ratio</span>
        {:else}
          <span>Link to aspect ratio</span>
        {/if}
      </Tooltip>
    </div>

    <input
      type="number"
      bind:value={calculatedHeight}
      on:keyup={(e) => handleChange('height')}
      on:change={() => handleChange('height')}
    />
    <Button
      rounded="full"
      variant="danger"
      size="xs"
      class="px-4 text-[0.5rem] xs:text-xs"
      on:click={resetValues}>Reset</Button
    >
  </div>
</div>

<style>
  input {
    @apply w-16 bg-transparent text-center;
  }

  input:focus {
    @apply outline-none;
  }
</style>
