<script lang="ts" module>
  import type { Snippet } from 'svelte';
  import { type VariantProps, tv } from 'tailwind-variants';

  import type {
    HTMLAnchorAttributes,
    HTMLButtonAttributes,
  } from 'svelte/elements';

  import { type WithElementRef } from '@slink/utils/ui/index.js';

  export const buttonVariants = tv({
    base: "inline-flex items-center justify-center text-center select-none cursor-pointer focus:outline-hidden focus-visible:outline-none whitespace-nowrap transition-all disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
    variants: {
      variant: {
        default:
          'border bg-none text-button-default dark:text-gray-200 border-bc-button-default dark:border-gray-700 hover:bg-button-default-hover dark:hover:bg-gray-800 hover:text-button-hover-default dark:hover:text-white',
        primary:
          'bg-button-accent/80 text-button-accent hover:bg-button-accent/100',
        secondary:
          'border bg-none text-button-default border-bc-button-default hover:bg-button-accent hover:text-button-accent hover:border-button-accent',
        dark: 'bg-button-dark/80 text-button-accent hover:bg-button-dark/100',
        invisible:
          'bg-none text-button-invisible hover:bg-button-invisible-hover/25 hover:text-button-primary',
        outline:
          'border bg-none text-button-default border-bc-button-default hover:bg-button-default-hover hover:text-button-hover-default',
        form: 'border bg-input-default text-input-default focus:outline-hidden focus:ring-3 border-bc-input-default hover:bg-input-default-hover focus:border-bc-input-default-focus focus:ring-rc-input-default-focus',
        link: 'bg-transparent dark:bg-transparent underline-offset-4 hover:underline text-button-default hover:bg-transparent',
        success:
          'bg-success/80 text-button-accent hover:bg-success focus:ring-3 focus:ring-rc-success/40',
        danger:
          'bg-danger/80 text-button-accent hover:bg-danger focus:ring-3 focus:ring-rc-danger/40',
        warning:
          'bg-warning/80 text-button-accent hover:bg-warning focus:ring-3 focus:ring-rc-warning/40',
        modern:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-full shadow-sm hover:shadow-md transition-all duration-200 font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white',
        glass:
          'bg-white/80 dark:bg-gray-900/80 border border-gray-200/60 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-200/40 dark:hover:shadow-gray-900/40 focus-visible:ring-blue-500/20 transition-all duration-200',
        'gradient-blue':
          'bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 !p-0.5',
        'gradient-green':
          'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 !p-0.5',
        destructive:
          'bg-destructive shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60 text-white',
        ghost:
          'hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-gray-100',
      },
      size: {
        xs: 'text-xs px-3 py-1.5 has-[>svg]:px-2',
        sm: 'text-xs px-3.5 py-2 has-[>svg]:px-2.5',
        md: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        lg: 'text-base px-6 py-3 has-[>svg]:px-4',
        default: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        icon: 'size-9',
      },
      gradientInnerSize: {
        xs: 'text-xs px-3 py-1.5 has-[>svg]:px-2',
        sm: 'text-xs px-3.5 py-2 has-[>svg]:px-2.5',
        md: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        lg: 'text-base px-6 py-3 has-[>svg]:px-4',
        default: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        icon: 'size-9',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
      fontWeight: {
        light: 'font-light',
        medium: 'font-medium',
        bold: 'font-bold',
      },
      motion: {
        none: 'transition-none',
        subtle: 'hover:scale-105 active:scale-95',
        'hover:opacity':
          'transition-opacity duration-300 ease-in-out opacity-75 hover:opacity-100',
        'hover:scale':
          'transition-transform duration-300 ease-in-out transform hover:scale-110',
        'hover:translate':
          'transition-transform duration-300 ease-in-out transform hover:translate-y-1',
        'hover:rotate':
          'transition-transform duration-300 ease-in-out transform hover:rotate-180',
      },
      status: {
        active: 'active',
        disabled: 'cursor-not-allowed pointer-events-none opacity-70',
      },
    },
    compoundVariants: [
      {
        variant: ['gradient-blue', 'gradient-green'],
        class: 'border-0',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'default',
      rounded: 'lg',
      fontWeight: 'medium',
      motion: 'none',
      status: 'active',
    },
  });

  export const gradientInnerVariants = tv({
    base: 'w-full h-full flex items-center justify-center transition-all',
    variants: {
      variant: {
        'gradient-blue':
          'bg-white/80 dark:bg-gray-900/80 text-blue-600 dark:text-blue-400 hover:bg-white dark:hover:bg-gray-800',
        'gradient-green':
          'bg-white/80 dark:bg-gray-900/80 text-green-600 dark:text-green-400 hover:bg-white dark:hover:bg-gray-800',
      },
      size: {
        xs: 'text-xs px-3 py-1.5 has-[>svg]:px-2',
        sm: 'text-xs px-3.5 py-2 has-[>svg]:px-2.5',
        md: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        lg: 'text-base px-6 py-3 has-[>svg]:px-4',
        default: 'text-sm px-5 py-2.5 has-[>svg]:px-3.5',
        icon: 'size-9',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      size: 'default',
      rounded: 'lg',
    },
  });

  export type ButtonVariant = VariantProps<typeof buttonVariants>['variant'];
  export type ButtonSize = VariantProps<typeof buttonVariants>['size'];
  export type ButtonRounded = VariantProps<typeof buttonVariants>['rounded'];
  export type ButtonFontWeight = VariantProps<
    typeof buttonVariants
  >['fontWeight'];
  export type ButtonMotion = VariantProps<typeof buttonVariants>['motion'];
  export type ButtonStatus = VariantProps<typeof buttonVariants>['status'];

  export type ButtonProps = WithElementRef<HTMLButtonAttributes> &
    WithElementRef<HTMLAnchorAttributes> & {
      variant?: ButtonVariant;
      size?: ButtonSize;
      rounded?: ButtonRounded;
      fontWeight?: ButtonFontWeight;
      motion?: ButtonMotion;
      status?: ButtonStatus;
      loading?: boolean;
      leftIcon?: Snippet<[]>;
      rightIcon?: Snippet<[]>;
      loadingIcon?: Snippet<[]>;
    };
</script>

<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import ButtonIcon from './button-icon.svelte';

  let {
    class: customClass,
    variant = 'default',
    size = 'default',
    rounded = 'lg',
    fontWeight = 'medium',
    motion = 'none',
    status = 'active',
    loading = false,
    leftIcon,
    rightIcon,
    loadingIcon,
    ref = $bindable(null),
    href = undefined,
    target,
    type = 'button',
    disabled,
    children,
    onclick,
    ...restProps
  }: ButtonProps = $props();

  let currentStatus = $derived(disabled ? 'disabled' : status);
  let isButtonDisabled = $derived(disabled || loading);
  let isGradientVariant = $derived(
    variant === 'gradient-blue' || variant === 'gradient-green',
  );

  let classes = $derived(
    className(
      `${buttonVariants({
        variant,
        size: isGradientVariant ? undefined : size,
        rounded,
        fontWeight,
        motion,
        status: currentStatus,
      })} ${customClass || ''}`,
    ),
  );

  let innerClasses = $derived(
    isGradientVariant
      ? gradientInnerVariants({
          variant: variant as 'gradient-blue' | 'gradient-green',
          size,
          rounded,
        })
      : '',
  );

  const handleClick = (e: any) => {
    if (isButtonDisabled) {
      return;
    }
    onclick?.(e);
  };
</script>

{#if href}
  <a
    bind:this={ref}
    data-slot="button"
    class={classes}
    href={disabled ? undefined : href}
    {target}
    aria-disabled={disabled}
    role={disabled ? 'link' : undefined}
    tabindex={disabled ? -1 : undefined}
    {...restProps}
  >
    {#if isGradientVariant}
      <div class={innerClasses}>
        {#if !leftIcon && !rightIcon}
          {@render children?.()}
          <ButtonIcon {loading} {loadingIcon} />
        {:else}
          <div class="flex w-full items-center justify-between gap-2">
            {#if leftIcon}
              <ButtonIcon {loading} {loadingIcon}>
                {@render leftIcon()}
              </ButtonIcon>
            {/if}
            {@render children?.()}
            {#if rightIcon}
              <ButtonIcon {loading} {loadingIcon}>
                {@render rightIcon()}
              </ButtonIcon>
            {/if}
          </div>
        {/if}
      </div>
    {:else if !leftIcon && !rightIcon}
      {@render children?.()}
      <ButtonIcon {loading} {loadingIcon} />
    {:else}
      <div class="flex w-full items-center justify-between gap-2">
        {#if leftIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render leftIcon()}
          </ButtonIcon>
        {/if}
        {@render children?.()}
        {#if rightIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render rightIcon()}
          </ButtonIcon>
        {/if}
      </div>
    {/if}
  </a>
{:else}
  <button
    bind:this={ref}
    data-slot="button"
    class={classes}
    {type}
    disabled={isButtonDisabled}
    onclick={handleClick}
    {...restProps}
  >
    {#if isGradientVariant}
      <div class={innerClasses}>
        {#if !leftIcon && !rightIcon}
          {@render children?.()}
          <ButtonIcon {loading} {loadingIcon} />
        {:else}
          <div class="flex w-full items-center justify-between gap-2">
            {#if leftIcon}
              <ButtonIcon {loading} {loadingIcon}>
                {@render leftIcon()}
              </ButtonIcon>
            {/if}
            {@render children?.()}
            {#if rightIcon}
              <ButtonIcon {loading} {loadingIcon}>
                {@render rightIcon()}
              </ButtonIcon>
            {/if}
          </div>
        {/if}
      </div>
    {:else if !leftIcon && !rightIcon}
      {@render children?.()}
      <ButtonIcon {loading} {loadingIcon} />
    {:else}
      <div class="flex w-full items-center justify-between gap-2">
        {#if leftIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render leftIcon()}
          </ButtonIcon>
        {/if}
        {@render children?.()}
        {#if rightIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render rightIcon()}
          </ButtonIcon>
        {/if}
      </div>
    {/if}
  </button>
{/if}
