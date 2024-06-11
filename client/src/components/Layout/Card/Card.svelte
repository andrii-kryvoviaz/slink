<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { type CardProps, CardTheme } from '@slink/components/Layout';

  interface $$Props extends CardProps {
    class?: string;
  }

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'full';
  export let rounded: $$Props['rounded'] = 'md';
  export let shadow: $$Props['shadow'] = 'md';

  $: classes = `${CardTheme({
    variant,
    size,
    rounded,
    shadow,
  })} ${$$props.class}`;
</script>

<div class={className(classes)}>
  <slot />
</div>
