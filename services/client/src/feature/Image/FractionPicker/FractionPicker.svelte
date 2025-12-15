<script lang="ts">
  import {
    fractionPickerContainerTheme,
    fractionPickerItemTheme,
  } from './FractionPicker.theme';
  import {
    DEFAULT_FRACTION_OPTIONS,
    type FractionPickerProps,
  } from './FractionPicker.types';

  let {
    currentWidth,
    currentHeight,
    originalWidth,
    originalHeight,
    options = DEFAULT_FRACTION_OPTIONS,
    size = 'md',
    disabled = false,
    on,
  }: FractionPickerProps = $props();

  const hasChanges = $derived(
    currentWidth !== originalWidth || currentHeight !== originalHeight,
  );

  const activeFraction = $derived.by(() => {
    if (!hasChanges) return 1;
    const currentFraction = currentWidth / originalWidth;
    const match = options.find(
      (opt) => Math.abs(opt.value - currentFraction) < 0.01,
    );
    return match?.value ?? null;
  });

  const isActive = (optionValue: number): boolean => {
    if (activeFraction === null) return false;
    return Math.abs(optionValue - activeFraction) < 0.01;
  };

  const handleSelect = (optionValue: number) => {
    if (!disabled) {
      on?.change(optionValue);
    }
  };
</script>

<div class="space-y-1">
  <span class="block text-xs font-medium text-gray-700 dark:text-gray-300">
    Scale
  </span>
  <div
    class={fractionPickerContainerTheme({ size })}
    role="radiogroup"
    aria-label="Scale selection"
  >
    {#each options as option (option.value)}
      {@const active = isActive(option.value)}
      <button
        type="button"
        role="radio"
        aria-checked={active}
        aria-label={`Scale to ${option.label}`}
        {disabled}
        onclick={() => handleSelect(option.value)}
        class={fractionPickerItemTheme({
          variant: active ? 'active' : 'inactive',
          size,
        })}
      >
        {option.label}
      </button>
    {/each}
  </div>
</div>
