<script lang="ts">
  import type { VariantProps } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { filterChipValueVariants, filterChipVariants } from './filter.theme';

  type Variants = VariantProps<typeof filterChipVariants>;
  type ValueVariants = VariantProps<typeof filterChipValueVariants>;

  interface Props {
    icon?: string;
    label?: string;
    tone?: Variants['tone'];
    maxWidth?: ValueVariants['maxWidth'];
    class?: string;
    children?: Snippet;
  }

  let {
    icon,
    label,
    tone = 'muted',
    maxWidth = 'md',
    class: className,
    children,
  }: Props = $props();
</script>

<span class={cn(filterChipVariants({ tone }), className)}>
  {#if icon}
    <Icon {icon} class="w-3 h-3 shrink-0" />
  {/if}
  {#if label}
    <span>{label}</span>
  {/if}
  {#if children}
    <span class={filterChipValueVariants({ maxWidth })}>
      {@render children()}
    </span>
  {/if}
</span>
