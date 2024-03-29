<script lang="ts">
  import type { HTMLButtonAttributes } from 'svelte/elements';

  import { className } from '@slink/utils/ui/className';

  import {
    ButtonIcon,
    type ButtonProps,
    ButtonTheme,
  } from '@slink/components/Common';

  type target = '_blank' | '_self' | '_parent' | '_top' | undefined;
  type LinkAttributes = {
    href?: string;
    target?: target extends LinkAttributes['href'] ? target : never;
  };

  interface $$Props extends HTMLButtonAttributes, LinkAttributes, ButtonProps {
    key?: string;
    loading?: boolean;
  }

  export let href: $$Props['href'] = undefined;
  export let target: $$Props['target'] = undefined;
  export let variant: $$Props['variant'] = 'default';
  export let size: $$Props['size'] = 'md';
  export let rounded: $$Props['rounded'] = 'lg';
  export let fontWeight: $$Props['fontWeight'] = 'medium';
  export let motion: $$Props['motion'] = 'none';
  export let state: $$Props['state'] = 'active';
  export let disabled: $$Props['disabled'] = false;
  export let loading: $$Props['loading'] = false;

  $: state = disabled ? 'disabled' : 'active';
  $: disabled = loading ?? disabled;

  $: classes = `${ButtonTheme({
    variant,
    size,
    rounded,
    fontWeight,
    motion,
    state,
  })} ${$$props.class}`;

  let buttonIconProps = {};
  $: buttonIconProps = {
    loading,
    customLoadingIcon: $$slots.loadingIcon,
  };
</script>

{#if href}
  <a {href} {target} {...$$restProps} class={className(classes)} on:click>
    <slot />
  </a>
{:else}
  <button {...$$restProps} class={className(classes)} on:click>
    {#if !$$slots.leftIcon && !$$slots.rightIcon}
      <slot />
      <ButtonIcon {...buttonIconProps}>
        <slot name="loadingIcon" slot="loading" />
      </ButtonIcon>
    {:else}
      <div class="flex w-full items-center justify-between gap-2">
        {#if $$slots.leftIcon}
          <ButtonIcon {...buttonIconProps}>
            <slot name="loadingIcon" slot="loading" />
            <slot name="leftIcon" />
          </ButtonIcon>
        {/if}
        <slot />
        {#if $$slots.rightIcon}
          <ButtonIcon {...buttonIconProps}>
            <slot name="loadingIcon" slot="loading" />
            <slot name="rightIcon" />
          </ButtonIcon>
        {/if}
      </div>
    {/if}
  </button>
{/if}
