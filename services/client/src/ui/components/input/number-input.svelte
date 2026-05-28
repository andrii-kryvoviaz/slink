<script lang="ts">
  import Icon from '@iconify/svelte';
  import type { HTMLInputAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    type NumberInputSize,
    type NumberInputVariant,
    numberInputButtonGroupVariants,
    numberInputButtonVariants,
    numberInputContainerVariants,
    numberInputFieldVariants,
  } from './number-input.theme.js';

  interface Props extends Pick<
    HTMLInputAttributes,
    | 'id'
    | 'name'
    | 'placeholder'
    | 'disabled'
    | 'readonly'
    | 'required'
    | 'autofocus'
  > {
    value?: number;
    min?: number;
    max?: number;
    step?: number;
    size?: NumberInputSize;
    variant?: NumberInputVariant;
    hasError?: boolean;
    class?: string;
    inputRef?: HTMLInputElement;
    onchange?: (value: number) => void;
    onkeydown?: (event: KeyboardEvent) => void;
  }

  let {
    value = $bindable(0),
    min,
    max,
    step = 1,
    size = 'md',
    variant = 'default',
    hasError = false,
    disabled = false,
    readonly = false,
    class: className,
    inputRef = $bindable(),
    onchange,
    onkeydown,
    ...restProps
  }: Props = $props();

  let isAtMin = $derived(min !== undefined && value <= min);
  let isAtMax = $derived(max !== undefined && value >= max);

  let pendingInput = $state<string | null>(null);
  let displayValue = $derived(pendingInput ?? String(value));

  const clamp = (val: number): number => {
    let result = val;
    if (min !== undefined && result < min) result = min;
    if (max !== undefined && result > max) result = max;
    return result;
  };

  const commit = (next: number) => {
    pendingInput = null;
    value = next;
    onchange?.(next);
  };

  const increment = () => {
    if (disabled || readonly || isAtMax) return;
    commit(clamp(value + step));
  };

  const decrement = () => {
    if (disabled || readonly || isAtMin) return;
    commit(clamp(value - step));
  };

  const handleInputChange = (event: Event) => {
    const text = (event.target as HTMLInputElement).value;
    pendingInput = text;

    const parsed = parseFloat(text);
    if (!isNaN(parsed)) {
      value = parsed;
      onchange?.(parsed);
    }
  };

  const handleBlur = () => {
    if (pendingInput === null) return;
    const parsed = parseFloat(pendingInput);
    commit(clamp(isNaN(parsed) ? (min ?? 0) : parsed));
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'ArrowUp') {
      event.preventDefault();
      increment();
    } else if (event.key === 'ArrowDown') {
      event.preventDefault();
      decrement();
    }

    onkeydown?.(event);
  };

  const handleWheel = (event: WheelEvent) => {
    if (document.activeElement !== inputRef) return;
    event.preventDefault();

    if (event.deltaY < 0) {
      increment();
    } else {
      decrement();
    }
  };

  const handleFocus = (event: FocusEvent) => {
    const target = event.target as HTMLInputElement;
    target.select();
  };

  let holdInterval: ReturnType<typeof setInterval> | null = null;
  let holdTimeout: ReturnType<typeof setTimeout> | null = null;

  const startHold = (action: () => void) => {
    action();
    holdTimeout = setTimeout(() => {
      holdInterval = setInterval(action, 75);
    }, 400);
  };

  const stopHold = () => {
    if (holdTimeout) {
      clearTimeout(holdTimeout);
      holdTimeout = null;
    }
    if (holdInterval) {
      clearInterval(holdInterval);
      holdInterval = null;
    }
  };
</script>

<div
  class={cn(numberInputContainerVariants({ size, disabled }), className)}
  role="group"
  aria-label="Number input"
>
  <input
    bind:this={inputRef}
    type="number"
    inputmode="numeric"
    {min}
    {max}
    {step}
    value={displayValue}
    {disabled}
    {readonly}
    class={numberInputFieldVariants({ variant, size, hasError })}
    oninput={handleInputChange}
    onblur={handleBlur}
    onkeydown={handleKeyDown}
    onwheel={handleWheel}
    onfocus={handleFocus}
    aria-valuenow={value}
    aria-valuemin={min}
    aria-valuemax={max}
    {...restProps}
  />

  <div class={numberInputButtonGroupVariants({ variant, size })}>
    <button
      type="button"
      tabindex={-1}
      disabled={disabled || readonly || isAtMax}
      class={numberInputButtonVariants({ variant, position: 'top', size })}
      onmousedown={() => startHold(increment)}
      onmouseup={stopHold}
      onmouseleave={stopHold}
      ontouchstart={() => startHold(increment)}
      ontouchend={stopHold}
      aria-label="Increase value"
    >
      <Icon icon="lucide:chevron-up" />
    </button>
    <button
      type="button"
      tabindex={-1}
      disabled={disabled || readonly || isAtMin}
      class={numberInputButtonVariants({ variant, position: 'bottom', size })}
      onmousedown={() => startHold(decrement)}
      onmouseup={stopHold}
      onmouseleave={stopHold}
      ontouchstart={() => startHold(decrement)}
      ontouchend={stopHold}
      aria-label="Decrease value"
    >
      <Icon icon="lucide:chevron-down" />
    </button>
  </div>
</div>
