<script lang="ts" module>
  import type { Snippet } from 'svelte';
  import { type VariantProps, tv } from 'tailwind-variants';

  import type {
    HTMLAnchorAttributes,
    HTMLButtonAttributes,
  } from 'svelte/elements';

  import { type WithElementRef } from '@slink/utils/ui/index.js';

  export const buttonVariants = tv({
    base: 'px-0.5 py-0.5 inline-flex items-center justify-center select-none cursor-pointer focus:outline-hidden focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50',
    variants: {
      variant: {
        default: 'border border-bc-button-default dark:border-gray-700',
        primary: 'bg-button-accent/80 hover:bg-button-accent/100',
        secondary:
          'border border-bc-button-default hover:bg-button-accent hover:border-button-accent',
        dark: 'bg-button-dark/80 hover:bg-button-dark/100',
        invisible: 'bg-none hover:bg-button-invisible-hover/25',
        outline:
          'border border-bc-button-default hover:bg-button-default-hover',
        form: 'border bg-input-default border-bc-input-default hover:bg-input-default-hover focus:border-bc-input-default-focus focus:ring-3 focus:ring-rc-input-default-focus',
        link: 'bg-transparent dark:bg-transparent hover:bg-transparent',
        success:
          'bg-success/80 hover:bg-success focus:ring-3 focus:ring-rc-success/40',
        danger:
          'bg-danger/80 hover:bg-danger focus:ring-3 focus:ring-rc-danger/40',
        warning:
          'bg-warning/80 hover:bg-warning focus:ring-3 focus:ring-rc-warning/40',
        modern:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-full shadow-sm hover:shadow-md transition-all duration-200',
        glass:
          'bg-white/80 dark:bg-gray-900/80 border border-gray-200/60 dark:border-gray-700/60 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-200/40 dark:hover:shadow-gray-900/40 focus-visible:ring-blue-500/20 transition-all duration-200',
        'gradient-blue':
          'bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 border-0 p-0.5 transition-all duration-500 ease-in-out focus:ring-3 focus:ring-blue-500/40',
        'gradient-green':
          'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 border-0 p-0.5 transition-all duration-500 ease-in-out focus:ring-3 focus:ring-green-500/40',
        destructive:
          'bg-destructive shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60',
        ghost: 'hover:bg-gray-100 dark:hover:bg-gray-800',
        transparent:
          'bg-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 focus:text-slate-900 dark:focus:text-slate-100 focus:bg-slate-100/50 dark:focus:bg-slate-800/50 transition-all duration-200',
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
    defaultVariants: {
      variant: 'default',
      size: 'default',
      rounded: 'lg',
      fontWeight: 'medium',
      motion: 'none',
      status: 'active',
    },
  });

  export const buttonInnerVariants = tv({
    base: 'w-full h-full flex items-center gap-2 justify-center text-center whitespace-nowrap transition-all [&_svg:not([class*="size-"])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0',
    variants: {
      variant: {
        default:
          'bg-transparent text-button-default dark:text-gray-200 hover:text-button-hover-default dark:hover:text-white',
        primary: 'bg-transparent text-button-accent',
        secondary:
          'bg-transparent text-button-default hover:text-button-accent',
        dark: 'bg-transparent text-button-accent',
        invisible:
          'bg-transparent text-button-invisible hover:text-button-primary',
        outline:
          'bg-transparent text-button-default hover:text-button-hover-default',
        form: 'bg-transparent text-input-default focus:outline-hidden',
        link: 'bg-transparent text-button-default underline-offset-4 hover:underline',
        success: 'bg-transparent text-button-accent',
        danger: 'bg-transparent text-button-accent',
        warning: 'bg-transparent text-button-accent',
        modern:
          'bg-transparent font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white',
        glass:
          'bg-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100',
        'gradient-blue':
          'bg-white/20 dark:bg-gray-900/70 text-white dark:text-blue-200',
        'gradient-green':
          'bg-white/80 dark:bg-gray-900/80 text-green-600 dark:text-green-400',
        destructive: 'bg-transparent text-white',
        ghost:
          'bg-transparent text-gray-900 hover:text-gray-900 dark:text-gray-100 dark:hover:text-gray-100',
        transparent: 'bg-transparent',
      },
      size: {
        xs: 'text-xs px-2.5 py-1 has-[>svg]:px-1.5',
        sm: 'text-xs px-3 py-1.5 has-[>svg]:px-2',
        md: 'text-sm px-4.5 py-2 has-[>svg]:px-3',
        lg: 'text-base px-5.5 py-2.5 has-[>svg]:px-3.5',
        default: 'text-sm px-4.5 py-2 has-[>svg]:px-3',
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
      variant: 'default',
      size: 'default',
      rounded: 'lg',
    },
  });

  export type ButtonVariant = VariantProps<typeof buttonVariants>['variant'];
  export type ButtonSize = VariantProps<typeof buttonInnerVariants>['size'];
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

  import ButtonContent from './button-content.svelte';

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

  let classes = $derived(
    className(
      `${buttonVariants({
        variant,
        rounded,
        fontWeight,
        motion,
        status: currentStatus,
      })} ${customClass || ''}`,
    ),
  );

  let innerClasses = $derived(
    buttonInnerVariants({
      variant,
      size,
      rounded,
    }),
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
    <div class={innerClasses}>
      <ButtonContent
        {loading}
        {leftIcon}
        {rightIcon}
        {loadingIcon}
        {children}
      />
    </div>
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
    <div class={innerClasses}>
      <ButtonContent
        {loading}
        {leftIcon}
        {rightIcon}
        {loadingIcon}
        {children}
      />
    </div>
  </button>
{/if}
