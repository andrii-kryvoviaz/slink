<script lang="ts" context="module">
  import { className } from '@slink/utils/className';
  import { cva } from 'class-variance-authority';
  import type { VariantProps } from 'class-variance-authority';

  const buttonVariants = cva(
    `inline-flex items-center justify-center text-center select-none cursor-pointer focus:outline-none focus-visable:outline-none`,
    {
      variants: {
        variant: {
          default:
            'border bg-none text-button-default border-button-default hover:bg-button-hover-default hover:text-button-hover-default',
          primary:
            'bg-button-accent text-button-accent bg-opacity-80 hover:bg-opacity-100',
          secondary:
            'border bg-none text-button-default border-button-default hover:bg-button-accent hover:text-button-accent hover:border-button-accent',
          invisible:
            'rounded bg-none text-button-accent hover:bg-button-accent',
          link: 'bg-transparent dark:bg-transparent underline-offset-4 hover:underline text-button-default hover:bg-transparent',
        },
        size: {
          xs: 'text-xs px-3 py-1.5',
          sm: 'text-xs px-3.5 py-2',
          md: 'text-sm px-5 py-2.5',
          lg: 'text-base px-6 py-3',
        },
        rounded: {
          none: 'rounded-none',
          sm: 'rounded',
          md: 'rounded-md',
          lg: 'rounded-lg',
          xl: 'rounded-xl',
          full: 'rounded-full',
        },
        fontWeight: {
          light: 'font-light',
          medium: 'font-medium',
          bold: 'font-bold',
        },
      },
    }
  );

  type ButtonProps = VariantProps<typeof buttonVariants>;

  export type ButtonSize = ButtonProps['size'];
  export type ButtonVariant = ButtonProps['variant'];
</script>

<script lang="ts">
  import type { HTMLButtonAttributes } from 'svelte/elements';

  type target = '_blank' | '_self' | '_parent' | '_top';
  type LinkAttributes = {
    href?: string;
    target?: target extends LinkAttributes['href'] ? target : never;
  };

  interface $$Props extends HTMLButtonAttributes, LinkAttributes, ButtonProps {
    key?: string;
  }

  export let href: $$Props['href'] = undefined;
  export let target: $$Props['target'] = '_blank';
  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';
  export let rounded: $$Props['rounded'] = 'lg';
  export let fontWeight: $$Props['fontWeight'] = 'medium';

  $: classes = `${buttonVariants({ variant, size, rounded, fontWeight })} ${
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
