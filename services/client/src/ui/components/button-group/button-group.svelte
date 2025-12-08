<script lang="ts" module>
  import type { Snippet } from 'svelte';
  import { type VariantProps, tv } from 'tailwind-variants';

  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef } from '@slink/utils/ui/index.js';

  export const buttonGroupVariants = tv({
    base: 'inline-flex items-center p-0',
    variants: {
      variant: {
        default:
          'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700',
        glass:
          'bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60',
        ghost: 'bg-transparent p-0',
        solid: 'bg-gray-100 dark:bg-gray-800',
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
        md: 'gap-2',
      },
    },
    defaultVariants: {
      variant: 'glass',
      rounded: 'lg',
      size: 'md',
      gap: 'none',
    },
  });

  export const buttonGroupItemVariants = tv({
    base: 'relative flex flex-1 items-center justify-center transition-all duration-150 focus:outline-none focus-visible:z-10 disabled:pointer-events-none disabled:opacity-50',
    variants: {
      variant: {
        default:
          'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700',
        primary: 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800',
        secondary:
          'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
        ghost:
          'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100/80 dark:hover:bg-gray-700/80',
        destructive:
          'text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30',
      },
      size: {
        sm: 'h-8 min-w-8 px-2 text-xs',
        md: 'h-9 min-w-9 px-2.5 text-sm',
        lg: 'h-10 min-w-10 px-3 text-sm',
        xl: 'h-11 min-w-11 px-3.5 text-base',
      },
      position: {
        first: 'rounded-l-md',
        middle: 'rounded-none',
        last: 'rounded-r-md',
        only: 'rounded-md',
      },
      active: {
        true: 'bg-gray-100 dark:bg-gray-700',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'primary',
        position: 'first',
        class: 'rounded-l-md',
      },
      {
        variant: 'primary',
        position: 'last',
        class: 'rounded-r-md',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
      position: 'middle',
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

  export type ButtonGroupItemVariant = VariantProps<
    typeof buttonGroupItemVariants
  >['variant'];
  export type ButtonGroupItemPosition = VariantProps<
    typeof buttonGroupItemVariants
  >['position'];

  export type ButtonGroupProps = WithElementRef<
    HTMLAttributes<HTMLDivElement>
  > & {
    variant?: ButtonGroupVariant;
    size?: ButtonGroupSize;
    rounded?: ButtonGroupRounded;
    gap?: ButtonGroupGap;
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
    ref = $bindable(null),
    children,
    ...restProps
  }: ButtonGroupProps & { children?: Snippet } = $props();
</script>

<div
  bind:this={ref}
  role="group"
  class={cn(buttonGroupVariants({ variant, size, rounded, gap }), customClass)}
  {...restProps}
>
  {@render children?.()}
</div>
