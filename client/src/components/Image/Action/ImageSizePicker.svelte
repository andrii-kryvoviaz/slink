<script lang="ts">
  import Icon from '@iconify/svelte';
  import type { ImageSize } from '@slink/components/Image/Types/ImageParams';
  import Button from '@slink/components/Shared/Action/Button.svelte';
  import { Tooltip } from 'flowbite-svelte';
  import { createEventDispatcher } from 'svelte';

  export let width: number;
  export let height: number;

  const dispatch = createEventDispatcher();

  let calculatedWidth = 0;
  let calculatedHeight = 0;

  let aspectRatioLinked = true;
  let aspectRatio = 0;

  const adjustBooundaries = () => {
    if (calculatedWidth < 5) {
      calculatedWidth = 5;
    }

    if (calculatedWidth > width) {
      calculatedWidth = width;
    }

    if (calculatedHeight < 5) {
      calculatedHeight = 5;
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
    adjustBooundaries();

    const sizeChange: Partial<ImageSize> = {};

    if (calculatedWidth !== width) {
      sizeChange.width = calculatedWidth;
    }

    if (calculatedHeight !== height) {
      sizeChange.height = calculatedHeight;
    }

    if (Object.keys(sizeChange).length > 0) {
      // onChange(sizeChange);
      dispatch('change', sizeChange);

      return;
    }

    // fallback to original values
    dispatch('change');
    // onChange();
  };

  const handleChange = (caller: 'width' | 'height') => {
    adjustBooundaries();

    if (aspectRatioLinked) {
      if (caller === 'width') {
        calculatedHeight = Math.floor(calculatedWidth / aspectRatio);
      }
      if (caller === 'height') {
        calculatedWidth = Math.floor(calculatedHeight * aspectRatio);
      }
    }

    handleSubmit();
  };

  $: if (width && height) {
    initializeValues();

    aspectRatio = width / height;
  }
</script>

<div class="flex gap-4 text-xs">
  <div class="flex gap-2 rounded-full border border-button-default p-1">
    <input
      type="number"
      bind:value={calculatedWidth}
      on:keyup={() => handleChange('width')}
      on:change={() => handleChange('width')}
    />
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
      class="px-4"
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
