<script lang="ts">
  import {
    type ButtonAttributes,
    ButtonIcon,
    ButtonTheme,
  } from '@slink/legacy/UI/Action';
  import type { Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  interface Props extends ButtonAttributes {
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    loadingIcon?: Snippet<[]>;
  }

  let {
    href,
    target,
    disabled = false,
    loading = false,
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    fontWeight = 'medium',
    motion = 'none',
    status = 'active',
    onclick,
    leftIcon,
    rightIcon,
    loadingIcon,
    children,
    ...props
  }: Props = $props();

  let currentStatus = $derived(disabled ? 'disabled' : status);

  let classes = $derived(
    className(
      `${ButtonTheme({
        variant,
        size,
        rounded,
        fontWeight,
        motion,
        status: currentStatus,
      })} ${props.class}`,
    ),
  );

  let isButtonDisabled = $derived(disabled || loading);

  const handleClick = (e: any) => {
    if (isButtonDisabled) {
      return;
    }

    onclick?.(e);
  };
</script>

{#if href}
  <a {href} {target} {...props} class={classes}>
    {@render children?.()}
  </a>
{:else}
  <button type="button" {...props} class={classes} onclick={handleClick}>
    {#if !leftIcon && !rightIcon}
      {@render children?.()}
      <ButtonIcon {loading} {loadingIcon} />
    {:else}
      <div class="flex w-full items-center justify-between gap-2">
        {#if leftIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render leftIcon?.()}
          </ButtonIcon>
        {/if}
        {@render children?.()}
        {#if rightIcon}
          <ButtonIcon {loading} {loadingIcon}>
            {@render rightIcon?.()}
          </ButtonIcon>
        {/if}
      </div>
    {/if}
  </button>
{/if}
