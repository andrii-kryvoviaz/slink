<script lang="ts">
  import { type VariantProps, cva } from 'class-variance-authority';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  const overlayCheckboxVariants = cva(
    'flex items-center justify-center rounded-full border-2 shadow-md transition-all duration-150',
    {
      variants: {
        selected: {
          true: 'bg-blue-500 border-blue-500',
          false:
            'bg-white/90 dark:bg-gray-900/90 border-white dark:border-gray-700',
        },
        size: {
          sm: 'w-5 h-5',
          md: 'w-6 h-6',
          lg: 'w-7 h-7',
        },
      },
      defaultVariants: {
        selected: false,
        size: 'md',
      },
    },
  );

  type OverlayCheckboxVariants = VariantProps<typeof overlayCheckboxVariants>;

  interface Props {
    selected: boolean;
    size?: OverlayCheckboxVariants['size'];
    class?: string;
  }

  let { selected, size = 'md', class: className }: Props = $props();

  const iconSizes: Record<
    NonNullable<OverlayCheckboxVariants['size']>,
    string
  > = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
  };

  const iconSize = $derived(iconSizes[size ?? 'md']);
</script>

<div class={cn(overlayCheckboxVariants({ selected, size }), className)}>
  {#if selected}
    <Icon icon="heroicons:check" class="{iconSize} text-white" />
  {/if}
</div>
