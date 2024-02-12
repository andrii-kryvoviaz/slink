<script lang="ts">
  import type { HTMLButtonAttributes } from 'svelte/elements';

  import { className } from '@slink/utils/className';
  import { type ButtonProps, ButtonVariants } from '@slink/components/Common';

  type target = '_blank' | '_self' | '_parent' | '_top' | undefined;
  type LinkAttributes = {
    href?: string;
    target?: target extends LinkAttributes['href'] ? target : never;
  };

  interface $$Props extends HTMLButtonAttributes, LinkAttributes, ButtonProps {
    key?: string;
  }

  export let href: $$Props['href'] = undefined;
  export let target: $$Props['target'] = undefined;
  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';
  export let rounded: $$Props['rounded'] = 'lg';
  export let fontWeight: $$Props['fontWeight'] = 'medium';

  $: classes = `${ButtonVariants({ variant, size, rounded, fontWeight })} ${
    $$props.class
  }`;
</script>

{#if href}
  <a {href} {target} {...$$restProps} class={className(classes)} on:click>
    <slot />
  </a>
{:else}
  <button {...$$restProps} class={className(classes)} on:click>
    <slot />
  </button>
{/if}
