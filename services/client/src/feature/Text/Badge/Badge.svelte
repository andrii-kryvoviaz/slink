<script lang="ts">
  import { BadgeTheme } from '@slink/feature/Text/Badge/Badge.theme';
  import type { BadgeProps } from '@slink/feature/Text/Badge/Badge.types';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props extends BadgeProps {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'default',
    size = 'md',
    outline = false,
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(BadgeTheme({ variant, size, outline }), props.class),
  );
</script>

<span class={className(classes)}>
  {@render children?.()}
</span>
