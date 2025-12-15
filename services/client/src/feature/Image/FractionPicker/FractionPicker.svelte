<script lang="ts">
  import {
    fractionPickerContainerTheme,
    fractionPickerItemTheme,
    fractionPickerLabelTheme,
  } from './FractionPicker.theme';
  import {
    DEFAULT_FRACTION_OPTIONS,
    type FractionPickerProps,
  } from './FractionPicker.types';

  let {
    value,
    options = DEFAULT_FRACTION_OPTIONS,
    size = 'md',
    disabled = false,
    on,
  }: FractionPickerProps = $props();

  const isActive = (optionValue: number): boolean => {
    if (value === null) return false;
    return Math.abs(optionValue - value) < 0.01;
  };

  const handleSelect = (optionValue: number) => {
    if (!disabled) {
      on?.change(optionValue);
    }
  };
</script>

<div class="flex items-center gap-2">
  <span
    class={fractionPickerLabelTheme({ size }) +
      ' text-gray-500 dark:text-gray-400'}
  >
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
