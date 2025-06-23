<script lang="ts">
  import type { ToastItemProps } from '@slink/components/UI/Toast/ToastItem.types';
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import { ToastItemTheme } from '@slink/components/UI/Toast/ToastItem.theme';

  interface Props extends ToastItemProps {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'default' as const,
    size = 'lg' as const,
    rounded = 'lg' as const,
    class: customClass,
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(ToastItemTheme({ variant, size, rounded }), customClass),
  );
</script>

<div class={classes} {...props}>
  {@render children?.()}
</div>
