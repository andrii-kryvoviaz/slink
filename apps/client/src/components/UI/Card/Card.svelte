<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { type CardProps, CardTheme } from '@slink/components/UI/Card';

  interface Props extends CardProps {
    class?: string;
  }

  let {
    variant = 'default',
    size = 'full',
    rounded = 'md',
    shadow = 'md',
    children,
    ...props
  }: Props = $props();

  let classes = $derived(
    className(
      `${CardTheme({ variant, size, rounded, shadow })} ${props.class}`,
    ),
  );
</script>

<div class={classes}>
  {@render children?.()}
</div>
