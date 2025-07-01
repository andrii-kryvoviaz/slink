<script lang="ts">
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import { NoticeTheme } from '@slink/components/UI/Text/Notice/Notice.theme';
  import type { NoticeProps } from '@slink/components/UI/Text/Notice/Notice.types';

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
