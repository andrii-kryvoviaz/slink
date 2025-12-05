<script lang="ts">
  import { Label } from '@slink/ui/components/label';
  import type { Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import type { HTMLInputAttributes } from 'svelte/elements';
  import { slide } from 'svelte/transition';

  import type { ErrorList } from '@slink/api/Exceptions';

  import { cn } from '@slink/utils/ui/index.js';

  import { type InputVariants, inputVariants } from './enhanced-input.theme.js';
  import { Root as BaseInput } from './index.js';

  interface Props
    extends
      Omit<HTMLInputAttributes, 'size'>,
      Pick<InputVariants, 'size' | 'variant' | 'rounded'> {
    key?: string;
    label?: string;
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    topRightText?: Snippet<[]>;
    error?: string | ErrorList;
  }

  let {
    value = $bindable(),
    label,
    size = 'md',
    variant = 'modern',
    rounded = 'lg',
    error = undefined,
    leftIcon,
    rightIcon,
    topRightText,
    children,
    class: className,
    ...props
  }: Props = $props();

  const combinedClasses = cn(
    inputVariants({
      size,
      variant,
      rounded,
      hasLeftIcon: !!leftIcon,
      hasRightIcon: !!rightIcon,
    }),
    className,
  );
</script>

<div>
  <div class="flex items-center justify-between">
    {#if label}
      <Label for={props.id} class="mb-3">
        {label}
      </Label>
    {/if}

    {@render topRightText?.()}
  </div>

  <div class="relative">
    {#if leftIcon}
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-muted-foreground"
      >
        {@render leftIcon()}
      </div>
    {/if}

    <BaseInput
      type={props.type || 'text'}
      id={props.id}
      name={props.name}
      placeholder={props.placeholder}
      disabled={props.disabled}
      readonly={props.readonly}
      required={props.required}
      oninput={props.oninput}
      onchange={props.onchange}
      onblur={props.onblur}
      onfocus={props.onfocus}
      onkeydown={props.onkeydown}
      onkeyup={props.onkeyup}
      onkeypress={props.onkeypress}
      bind:value
      class={twMerge(combinedClasses)}
      aria-invalid={error ? true : undefined}
    />

    {#if rightIcon}
      <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-muted-foreground"
      >
        {@render rightIcon()}
      </div>
    {/if}

    {@render children?.()}
  </div>

  {#if error}
    <div
      class="mt-1.5 text-xs text-input-error"
      transition:slide={{ duration: 150 }}
    >
      {#if typeof error === 'string'}
        {error}
      {/if}
    </div>
  {/if}
</div>
