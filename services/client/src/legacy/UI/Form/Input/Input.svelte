<script lang="ts">
  import { type InputProps, InputTheme } from '@slink/legacy/UI/Form';
  import type { Snippet } from 'svelte';

  import type { HTMLInputAttributes } from 'svelte/elements';

  import { className } from '@slink/utils/ui/className';

  interface Props extends Omit<HTMLInputAttributes, 'size'>, InputProps {
    key?: string;
    label?: string;
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    topRightText?: Snippet<[]>;
  }

  let {
    value = $bindable(),
    label,
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    error = undefined,
    leftIcon,
    rightIcon,
    topRightText,
    children,
    ...props
  }: Props = $props();

  let originalVariant = variant;
  let inputVariant = $derived(error ? 'error' : originalVariant);

  let classes = $derived(
    className(
      `${InputTheme({
        variant: inputVariant,
        size,
        rounded,
      })} ${props.class}`,
    ),
  );
</script>

<div>
  <div class="flex items-center justify-between">
    {#if label}
      <label for={props.id} class="block text-sm text-label-default">
        {label}
      </label>
    {/if}

    {@render topRightText?.()}
  </div>

  <div class="relative">
    {#if leftIcon}
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 opacity-60"
      >
        {@render leftIcon?.()}
      </div>
    {/if}
    <input
      {...props}
      bind:value
      class={classes}
      class:pl-10={leftIcon}
      class:pr-10={rightIcon}
    />
    {#if rightIcon}
      <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 opacity-60"
      >
        {@render rightIcon?.()}
      </div>
    {/if}

    {@render children?.()}
  </div>

  <div class="mt-1 text-xs text-input-error">
    {#if error && typeof error === 'string'}
      {error}
    {/if}
    &nbsp;
  </div>
</div>
