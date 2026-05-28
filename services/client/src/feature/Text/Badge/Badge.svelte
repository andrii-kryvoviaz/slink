<script lang="ts">
  import { BadgeTheme } from '@slink/feature/Text/Badge/Badge.theme';
  import type { BadgeProps } from '@slink/feature/Text/Badge/Badge.types';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';
  import type { HTMLAttributes } from 'svelte/elements';

  interface Props extends BadgeProps, HTMLAttributes<HTMLSpanElement> {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'default',
    size = 'md',
    outline = false,
    class: classOverride,
    children,
    ...rest
  }: Props = $props();

  let classes = $derived(
    className(BadgeTheme({ variant, size, outline }), classOverride),
  );
</script>

<span class={className(classes)} {...rest}>
  {@render children?.()}
</span>
