<script lang="ts" module>
  import type { Snippet } from 'svelte';
  import { type VariantProps, tv } from 'tailwind-variants';

  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef } from '@slink/utils/ui/index.js';

  export const buttonGroupVariants = tv({
    base: 'inline-flex items-center',
    variants: {
      variant: {
        default: 'bg-card border border-border',
        glass:
          'bg-card/90 dark:bg-muted/90 backdrop-blur shadow-[inset_0_0_0_1px_var(--color-border)]',
        ghost: 'bg-transparent',
        solid: 'bg-muted',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
      },
      size: {
        sm: '',
        md: '',
        lg: '',
        xl: '',
      },
      gap: {
        none: 'gap-0',
        xs: 'gap-0.5',
        sm: 'gap-1',
        md: 'gap-1.5',
      },
      padding: {
        none: 'p-0',
        xs: 'p-0.5',
        sm: 'p-1',
        md: 'p-1.5',
        lg: 'p-2',
      },
    },
    defaultVariants: {
      variant: 'glass',
      rounded: 'lg',
      size: 'md',
      gap: 'none',
      padding: 'sm',
    },
  });

  export const buttonGroupItemVariants = tv({
    base: 'relative flex items-center justify-center rounded-md transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-ring/50 focus-visible:z-10 disabled:pointer-events-none disabled:opacity-50',
    variants: {
      variant: {
        default:
          'text-muted-foreground hover:text-foreground hover:bg-ghost-hover-strong',
        primary:
          'bg-info-fill/80 text-info-fill-foreground hover:bg-info-fill/90 active:bg-info-fill',
        'primary-outline':
          'border border-info text-info-subtle-foreground hover:bg-info-fill/90 hover:text-info-fill-foreground active:bg-info-fill',
        secondary: 'text-foreground-soft hover:bg-ghost-hover-strong',
        ghost:
          'text-muted-foreground hover:text-foreground hover:bg-ghost-hover-strong/80',
        destructive:
          'text-muted-foreground hover:text-danger-subtle-foreground hover:bg-danger-subtle',
      },
      size: {
        sm: 'h-7 min-w-7 px-2 text-xs',
        md: 'h-8 min-w-8 px-2.5 text-sm',
        lg: 'h-9 min-w-9 px-3 text-sm',
        xl: 'h-10 min-w-10 px-3.5 text-base',
      },
      active: {
        true: 'bg-muted',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      active: false,
    },
  });

  export type ButtonGroupVariant = VariantProps<
    typeof buttonGroupVariants
  >['variant'];
  export type ButtonGroupSize = VariantProps<
    typeof buttonGroupVariants
  >['size'];
  export type ButtonGroupRounded = VariantProps<
    typeof buttonGroupVariants
  >['rounded'];
  export type ButtonGroupGap = VariantProps<typeof buttonGroupVariants>['gap'];
  export type ButtonGroupPadding = VariantProps<
    typeof buttonGroupVariants
  >['padding'];

  export type ButtonGroupItemVariant = VariantProps<
    typeof buttonGroupItemVariants
  >['variant'];

  export type ButtonGroupProps = WithElementRef<
    HTMLAttributes<HTMLDivElement>
  > & {
    variant?: ButtonGroupVariant;
    size?: ButtonGroupSize;
    rounded?: ButtonGroupRounded;
    gap?: ButtonGroupGap;
    padding?: ButtonGroupPadding;
  };
</script>

<script lang="ts">
  import { cn } from '@slink/utils/ui';

  let {
    class: customClass,
    variant = 'glass',
    size = 'md',
    rounded = 'lg',
    gap = 'none',
    padding = 'sm',
    ref = $bindable(null),
    children,
    ...restProps
  }: ButtonGroupProps & { children?: Snippet } = $props();
</script>

<div
  bind:this={ref}
  role="group"
  class={cn(
    buttonGroupVariants({ variant, size, rounded, gap, padding }),
    customClass,
  )}
  {...restProps}
>
  {@render children?.()}
</div>
