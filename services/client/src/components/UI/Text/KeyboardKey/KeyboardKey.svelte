<script lang="ts">
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import type { KeyboardKeyProps } from '@slink/components/UI/Text';
  import { KeyboardKeyTheme } from '@slink/components/UI/Text';

  interface Props extends KeyboardKeyProps {
    class?: string;
    children?: Snippet<[]>;
  }

  let {
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    fontWeight = 'medium',
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(
      `${KeyboardKeyTheme({
        variant,
        size,
        rounded,
        fontWeight,
      })} ${props.class}`,
    ),
  );
</script>

<kbd class={classes}>
  {@render children?.()}
</kbd>
