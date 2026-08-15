<script lang="ts" module>
  import type { Snippet } from 'svelte';
  import { type VariantProps, tv } from 'tailwind-variants';

  import type {
    HTMLAnchorAttributes,
    HTMLButtonAttributes,
  } from 'svelte/elements';

  import { type WithElementRef } from '@slink/utils/ui/index.js';

  export const buttonVariants = tv({
    base: 'inline-flex items-center justify-center select-none cursor-pointer focus:outline-hidden focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50',
    variants: {
      variant: {
        default: 'border border-border',
        primary: 'bg-info-fill/80 hover:bg-info-fill/100',
        secondary:
          'border border-muted hover:bg-info-fill hover:border-info-fill',
        dark: 'bg-surface-inverse/80 hover:bg-surface-inverse/100',
        accent:
          'bg-surface-inverse/80 hover:bg-surface-inverse/100 dark:bg-info-fill/80 dark:hover:bg-info-fill/100',
        invisible: 'bg-none hover:bg-muted-foreground/25',
        outline: 'border border-muted hover:bg-surface-raised',
        link: 'bg-transparent dark:bg-transparent hover:bg-transparent',
        danger:
          'bg-danger-fill/80 hover:bg-danger-fill focus:ring-3 focus:ring-danger/40',
        modern:
          'bg-card border border-border rounded-full shadow-sm hover:shadow-md transition-all duration-200',
        glass:
          'bg-card/80 border border-border/60 hover:bg-card dark:hover:bg-muted hover:border-border-strong hover:shadow-lg hover:shadow-border/40 dark:hover:shadow-card/40 focus-visible:ring-ring/20 transition-all duration-200',
        'glass-dark':
          'bg-surface-inverse-foreground/10 backdrop-blur-sm border border-surface-inverse-foreground/20 hover:bg-surface-inverse-foreground/20 transition-all duration-200',
        'primary-dark':
          'bg-info-fill border-transparent hover:bg-info-surface dark:hover:bg-info-surface-strong transition-all duration-200',
        'soft-violet':
          'text-decor-violet-subtle-foreground bg-decor-violet/10 dark:bg-decor-violet/15 hover:bg-decor-violet/20 dark:hover:bg-decor-violet/25 border border-decor-violet/20 hover:border-decor-violet/30 transition-all duration-200',
        'soft-indigo':
          'text-accent-subtle-foreground bg-accent-fill/10 dark:bg-accent-fill/15 hover:bg-accent-fill/20 dark:hover:bg-accent-fill/25 border border-accent/20 hover:border-accent/30 transition-all duration-200',
        'soft-blue':
          'text-info-subtle-foreground bg-info-fill/10 dark:bg-info-fill/15 hover:bg-info-fill/20 dark:hover:bg-info-fill/25 border border-info/20 hover:border-info/30 transition-all duration-200',
        'soft-green':
          'text-success-subtle-foreground bg-success/10 dark:bg-success/15 hover:bg-success/20 dark:hover:bg-success/25 border border-success/20 hover:border-success/30 transition-all duration-200',
        'soft-red':
          'text-danger-subtle-foreground bg-danger/10 dark:bg-danger/15 hover:bg-danger/20 dark:hover:bg-danger/25 border border-danger/20 hover:border-danger/30 transition-all duration-200',
        'soft-amber':
          'text-warning-subtle-foreground bg-warning/10 dark:bg-warning/15 hover:bg-warning/20 dark:hover:bg-warning/25 border border-warning/20 hover:border-warning/30 transition-all duration-200',
        'outline-blue':
          'bg-transparent border border-info hover:bg-info-subtle transition-all duration-200',
        'outline-green':
          'bg-transparent border border-success hover:bg-success-subtle transition-all duration-200',
        'outline-purple':
          'bg-transparent border border-accent hover:bg-accent-subtle transition-all duration-200',
        'outline-amber':
          'bg-transparent border border-warning hover:bg-warning-subtle transition-all duration-200',
        'outline-danger':
          'bg-transparent border border-danger hover:bg-danger-subtle transition-all duration-200',
        destructive:
          'bg-danger shadow-xs hover:bg-danger/90 focus-visible:ring-danger/20 dark:focus-visible:ring-danger/40 dark:bg-danger/60',
        ghost: 'hover:bg-ghost-hover',
        transparent:
          'bg-transparent text-muted-foreground hover:text-foreground-soft hover:bg-ghost-hover/50 dark:hover:bg-ghost-hover/30 focus:text-foreground focus:bg-ghost-hover/50 transition-all duration-200',
        toggle:
          'bg-gradient-to-br from-muted-subtle to-muted/50 dark:from-muted-subtle/50 dark:to-muted/30 border border-border hover:border-border-strong transition-all duration-200',
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
      padding: {
        none: 'p-0',
        xs: 'px-0.5 py-0.5',
        sm: 'p-1',
        md: 'p-1.5',
        lg: 'p-2',
      },
      status: {
        active: 'active',
        disabled: 'cursor-not-allowed pointer-events-none opacity-70',
      },
    },
    compoundVariants: [
      {
        variant: 'primary-dark',
        status: 'disabled',
        class: 'bg-transparent opacity-100',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'default',
      rounded: 'lg',
      fontWeight: 'medium',
      motion: 'none',
      padding: 'xs',
      status: 'active',
    },
  });

  export const buttonInnerVariants = tv({
    base: 'w-full h-full flex items-center justify-center text-center whitespace-nowrap transition-all [&_svg:not([class*="size-"])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0',
    variants: {
      spacing: {
        tight: 'gap-1',
        normal: 'gap-2',
        relaxed: 'gap-3',
        wide: 'gap-4',
      },
      variant: {
        default:
          'bg-transparent dark:text-foreground-soft dark:hover:text-foreground',
        primary: 'bg-transparent text-info-fill-foreground',
        secondary: 'bg-transparent hover:text-info-fill-foreground',
        dark: 'bg-transparent text-surface-inverse-foreground',
        accent: 'bg-transparent text-surface-inverse-foreground',
        invisible: 'bg-transparent',
        outline: 'bg-transparent',
        link: 'bg-transparent underline-offset-4 hover:underline',
        danger: 'bg-transparent text-danger-foreground',
        modern:
          'bg-transparent font-medium text-foreground-soft hover:text-foreground',
        glass:
          'bg-transparent text-muted-foreground-strong hover:text-foreground',
        'glass-dark': 'bg-transparent text-surface-inverse-foreground',
        'primary-dark': 'bg-transparent text-info-fill-foreground',
        'soft-violet': 'bg-transparent',
        'soft-indigo': 'bg-transparent',
        'soft-blue': 'bg-transparent',
        'soft-green': 'bg-transparent',
        'soft-red': 'bg-transparent',
        'soft-amber': 'bg-transparent',
        'outline-blue':
          'bg-transparent text-info-subtle-foreground dark:text-info',
        'outline-green':
          'bg-transparent text-success-subtle-foreground dark:text-success',
        'outline-purple':
          'bg-transparent text-accent-subtle-foreground dark:text-accent',
        'outline-amber':
          'bg-transparent text-warning-subtle-foreground dark:text-warning',
        'outline-danger':
          'bg-transparent text-danger-subtle-foreground dark:text-danger',
        destructive: 'bg-transparent text-danger-foreground',
        ghost: 'bg-transparent text-foreground hover:text-foreground',
        transparent: 'bg-transparent',
        toggle:
          'bg-transparent text-muted-foreground hover:text-foreground-soft',
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
      status: {
        active: '',
        disabled: '',
      },
    },
    compoundVariants: [
      {
        variant: 'primary-dark',
        status: 'disabled',
        class: 'text-info-fill-foreground/30',
      },
      {
        variant: 'toggle',
        size: 'xs',
        class: 'has-[>svg]:px-2.5',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'default',
      rounded: 'lg',
      status: 'active',
      spacing: 'normal',
    },
  });

  export type ButtonVariant = VariantProps<typeof buttonVariants>['variant'];
  export type ButtonSize = VariantProps<typeof buttonInnerVariants>['size'];
  export type ButtonSpacing = VariantProps<
    typeof buttonInnerVariants
  >['spacing'];
  export type ButtonRounded = VariantProps<typeof buttonVariants>['rounded'];
  export type ButtonFontWeight = VariantProps<
    typeof buttonVariants
  >['fontWeight'];
  export type ButtonMotion = VariantProps<typeof buttonVariants>['motion'];
  export type ButtonPadding = VariantProps<typeof buttonVariants>['padding'];
  export type ButtonStatus = VariantProps<typeof buttonVariants>['status'];

  export type ButtonProps = WithElementRef<HTMLButtonAttributes> &
    WithElementRef<HTMLAnchorAttributes> & {
      variant?: ButtonVariant;
      size?: ButtonSize;
      spacing?: ButtonSpacing;
      rounded?: ButtonRounded;
      fontWeight?: ButtonFontWeight;
      motion?: ButtonMotion;
      padding?: ButtonPadding;
      status?: ButtonStatus;
      loading?: boolean;
      loadingVariant?: 'spinner' | 'dots';
      justify?: 'between' | 'center';
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
    spacing = 'normal',
    rounded = 'lg',
    fontWeight = 'medium',
    motion = 'none',
    padding = 'xs',
    status = 'active',
    loading = false,
    loadingVariant,
    justify,
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
        padding,
        status: currentStatus,
      })} ${customClass || ''}`,
    ),
  );

  let innerClasses = $derived(
    buttonInnerVariants({
      variant,
      size,
      spacing,
      rounded,
      status: currentStatus,
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
        {loadingVariant}
        {justify}
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
        {loadingVariant}
        {justify}
        {leftIcon}
        {rightIcon}
        {loadingIcon}
        {children}
      />
    </div>
  </button>
{/if}
