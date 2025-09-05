<script lang="ts">
  import { Label } from '@slink/ui/components/label';
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import type { HTMLInputAttributes } from 'svelte/elements';

  import type { ErrorList } from '@slink/api/Exceptions';

  import { cn } from '@slink/utils/ui/index.js';

  import { Root as BaseInput } from './index.js';

  interface Props extends Omit<HTMLInputAttributes, 'size'> {
    key?: string;
    label?: string;
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    topRightText?: Snippet<[]>;
    error?: string | ErrorList;
    size?: 'sm' | 'md' | 'lg';
    variant?: 'default' | 'error' | 'modern';
    rounded?: 'sm' | 'md' | 'lg';
  }

  let {
    value = $bindable(),
    label,
    size = 'md',
    variant = 'default',
    rounded = 'lg',
    error = undefined,
    leftIcon,
    rightIcon,
    topRightText,
    children,
    class: className,
    ...props
  }: Props = $props();

  const inputVariants = cva(
    'aria-invalid:border-purple-300/60 dark:aria-invalid:border-purple-600/40 aria-invalid:focus-visible:border-purple-400/70 dark:aria-invalid:focus-visible:border-purple-500/50 aria-invalid:focus-visible:ring-purple-500/20 dark:aria-invalid:focus-visible:ring-purple-400/20 aria-invalid:bg-purple-25/30 dark:aria-invalid:bg-purple-950/20 aria-invalid:hover:bg-purple-50/40 dark:aria-invalid:hover:bg-purple-950/30 transition-all duration-200',
    {
      variants: {
        size: {
          sm: 'h-8 px-2 text-sm',
          md: 'h-9 px-3 text-sm',
          lg: 'h-10 px-4 text-base',
        },
        hasLeftIcon: {
          true: 'pl-10',
          false: '',
        },
        hasRightIcon: {
          true: 'pr-10',
          false: '',
        },
      },
      defaultVariants: {
        size: 'md',
        hasLeftIcon: false,
        hasRightIcon: false,
      },
    },
  );

  const combinedClasses = cn(
    inputVariants({
      size,
      hasLeftIcon: !!leftIcon,
      hasRightIcon: !!rightIcon,
    }),
    className,
  );
</script>

<div>
  <div class="flex items-center justify-between">
    {#if label}
      <Label for={props.id} class="mb-2">
        {label}
      </Label>
    {/if}

    {@render topRightText?.()}
  </div>

  <div class="relative">
    {#if leftIcon}
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground"
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
      bind:value
      class={twMerge(combinedClasses)}
      aria-invalid={error ? true : undefined}
    />

    {#if rightIcon}
      <div
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground"
      >
        {@render rightIcon()}
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
