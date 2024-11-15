<script lang="ts">
  import { className } from '@slink/utils/ui/className';

  import {
    type ButtonAttributes,
    ButtonIcon,
    ButtonTheme,
  } from '@slink/components/UI/Action';

  interface $$Props extends ButtonAttributes {}

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
  $: disabled = loading || disabled;

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
  <button type="button" {...$$restProps} class={className(classes)} on:click>
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
