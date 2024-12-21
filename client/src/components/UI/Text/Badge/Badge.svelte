<script lang="ts">
  import type { BadgeProps } from '@slink/components/UI/Text/Badge/Badge.types';
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import { BadgeTheme } from '@slink/components/UI/Text/Badge/Badge.theme';

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
