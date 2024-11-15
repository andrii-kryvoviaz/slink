<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import { type LoaderProps, LoaderTheme } from '@slink/components/UI/Loader';

  interface $$Props extends LoaderProps {
    class?: string;
  }

  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'sm';
  export let speed: $$Props['speed'] = 'default';

  const defaultOuterClasses = 'flex items-center justify-center gap-4';

  $: innerClasses = `${LoaderTheme({
    variant,
    size,
    speed,
  })}`;

  $: outerClasses = className(defaultOuterClasses, $$props.class);
</script>

<div class={outerClasses}>
  <div {...$$props} class={className(innerClasses)} />
  {#if $$slots.default}
    <slot />
  {/if}
</div>
