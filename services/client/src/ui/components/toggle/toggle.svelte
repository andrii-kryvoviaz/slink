<script lang="ts">
  import { Switch as SwitchPrimitive } from 'bits-ui';
  import { type VariantProps, cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';
  import type { WithoutChildrenOrChild } from '@slink/utils/ui/index.js';

  const toggleVariants = cva(
    'data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:border-ring focus-visible:ring-ring/50 dark:data-[state=unchecked]:bg-input/80 shadow-xs peer inline-flex shrink-0 items-center rounded-full border border-transparent outline-none transition-all focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
    {
      variants: {
        size: {
          xs: 'h-3 w-5',
          sm: 'h-4 w-7',
          md: 'h-[1.15rem] w-8',
          lg: 'h-6 w-10',
        },
      },
      defaultVariants: {
        size: 'md',
      },
    },
  );

  const thumbVariants = cva(
    'bg-background dark:data-[state=unchecked]:bg-foreground dark:data-[state=checked]:bg-primary-foreground pointer-events-none block rounded-full ring-0 transition-transform data-[state=unchecked]:translate-x-0',
    {
      variants: {
        size: {
          xs: 'size-2 data-[state=checked]:translate-x-[calc(100%-1px)]',
          sm: 'size-3 data-[state=checked]:translate-x-[calc(100%-1px)]',
          md: 'size-4 data-[state=checked]:translate-x-[calc(100%-2px)]',
          lg: 'size-5 data-[state=checked]:translate-x-[calc(100%-2px)]',
        },
      },
      defaultVariants: {
        size: 'md',
      },
    },
  );

  type ToggleVariants = VariantProps<typeof toggleVariants>;

  interface Props
    extends WithoutChildrenOrChild<SwitchPrimitive.RootProps>,
      ToggleVariants {
    ref?: HTMLButtonElement | null;
    class?: string;
    checked?: boolean;
    size?: ToggleVariants['size'];
    preIcon?: Snippet;
    postIcon?: Snippet;
    on?: {
      change?: (checked: boolean) => void;
    };
  }

  let {
    ref = $bindable(null),
    class: className,
    checked = $bindable(false),
    size = 'md',
    preIcon,
    postIcon,
    on,
    ...restProps
  }: Props = $props();

  const handleCheckedChange = (value: boolean) => {
    checked = value;
    on?.change?.(value);
  };
</script>

<label class="flex cursor-pointer items-center gap-2">
  {@render preIcon?.()}
  <SwitchPrimitive.Root
    bind:ref
    bind:checked
    onCheckedChange={handleCheckedChange}
    data-slot="toggle"
    class={cn(toggleVariants({ size }), className)}
    {...restProps}
  >
    <SwitchPrimitive.Thumb
      data-slot="toggle-thumb"
      class={cn(thumbVariants({ size }))}
    />
  </SwitchPrimitive.Root>
  {@render postIcon?.()}
</label>
