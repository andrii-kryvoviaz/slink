<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { LoaderTheme } from '@slink/components/Common/Loader/Loader.theme';
  import type { LoaderProps } from '@slink/components/Common/Loader/Loader.types';

  interface $$Props extends LoaderProps {}

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'sm';
  export let speed: $$Props['speed'] = 'default';

  $: classes = `${LoaderTheme({
    variant,
    size,
    speed,
  })} ${$$props.class}`;
</script>

<div class="flex flex-grow items-center justify-center gap-4">
  <div {...$$props} class={className(classes)} />
  {#if $$slots.default}
    <slot />
  {/if}
</div>
