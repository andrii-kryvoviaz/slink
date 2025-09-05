<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import DatePicker from './date-picker.svelte';

  interface Props {
    value?: string | null;
    label?: string;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    leftIcon?: Snippet<[]>;
    class?: string;
    id?: string;
    name?: string;
    required?: boolean;
  }

  let {
    value = $bindable(),
    label,
    placeholder = 'Select a date',
    error = undefined,
    disabled = false,
    leftIcon,
    class: classNameProp = '',
    id,
    name,
    required = false,
    ...restProps
  }: Props = $props();
</script>

<div class="space-y-2">
  {#if label}
    <label for={id} class="block text-sm font-medium text-foreground">
      {label}
      {#if required}
        <span class="text-destructive ml-1">*</span>
      {/if}
    </label>
  {/if}

  <div class="relative">
    {#if leftIcon}
      <div
        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 pointer-events-none"
      >
        {@render leftIcon()}
      </div>
    {/if}

    <DatePicker
      bind:value
      {placeholder}
      {disabled}
      {id}
      {name}
      {leftIcon}
      class={cn(
        leftIcon && 'pl-10',
        error && 'border-destructive focus-visible:ring-destructive/20',
        classNameProp,
      )}
      aria-describedby={error ? `${id}-error` : undefined}
      aria-invalid={!!error}
      {...restProps}
    />
  </div>

  {#if error}
    <p id={error ? `${id}-error` : undefined} class="text-sm text-destructive">
      {error}
    </p>
  {/if}
</div>
