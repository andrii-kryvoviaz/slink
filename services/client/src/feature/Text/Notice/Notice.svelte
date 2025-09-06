<script lang="ts">
  import { NoticeTheme } from '@slink/feature/Text/Notice/Notice.theme';
  import type { NoticeProps } from '@slink/feature/Text/Notice/Notice.types';
  import type { Snippet } from 'svelte';

  import { className } from '$lib/utils/ui/className';

  interface Props extends NoticeProps {
    class?: string;
    children?: Snippet;
  }

  let {
    variant = 'success',
    size = 'md',
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(NoticeTheme({ variant, size }), props.class),
  );
</script>

<div class={classes}>
  {@render children?.()}
</div>
