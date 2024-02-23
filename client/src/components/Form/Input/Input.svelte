<script lang="ts">
  import type { HTMLInputAttributes as BaseHTMLInputAttributes } from 'svelte/elements';

  import { className } from '@slink/utils/ui/className';

  import { type InputProps, InputTheme } from '@slink/components/Form';

  type HTMLInputAttributes = Omit<BaseHTMLInputAttributes, 'size'>;

  interface $$Props extends HTMLInputAttributes, InputProps {
    key?: string;
    label?: string;
    error?: string | boolean;
  }

  export let label: $$Props['label'] = '';
  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';
  export let rounded: $$Props['rounded'] = 'lg';
  export let error: $$Props['error'] = false;

  let originalVariant = variant;
  $: variant = error ? 'error' : originalVariant;

  $: classes = `${InputTheme({
    variant,
    size,
    rounded,
  })} ${$$props.class}`;
</script>

<div>
  <div class="flex items-center justify-between">
    {#if label}
      <label for={$$props['id']} class="block text-sm text-label-default">
        {label}
      </label>
    {/if}

    <slot name="topRightText" />
  </div>

  <div class="relative">
    {#if $$slots.leftIcon}
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 opacity-60"
      >
        <slot name="leftIcon" />
      </div>
    {/if}
    <input
      {...$$props}
      class={className(classes)}
      class:pl-10={$$slots.leftIcon}
      class:pr-10={$$slots.rightIcon}
    />
    {#if $$slots.rightIcon}
      <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 opacity-60"
      >
        <slot name="rightIcon" />
      </div>
    {/if}
  </div>

  {#if error && typeof error === 'string'}
    <div class="mt-1 text-xs text-input-error">
      {error}
    </div>
  {/if}
</div>
