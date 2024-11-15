<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { BadgeTheme } from '@slink/components/UI/Text/Badge/Badge.theme';
  import type { BadgeProps } from '@slink/components/UI/Text/Badge/Badge.types';

  interface $$Props extends BadgeProps {
    class?: string;
  }

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';
  export let outline: $$Props['outline'] = false;

  $: classes = `${BadgeTheme({
    variant,
    size,
    outline,
  })} ${$$props.class}`;
</script>

<span class={className(classes)}>
  <slot />
</span>
