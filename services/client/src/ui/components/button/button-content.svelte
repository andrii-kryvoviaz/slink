<script lang="ts">
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import ButtonIcon from './button-icon.svelte';

  const contentVariants = cva('flex w-full items-center gap-2', {
    variants: {
      justify: {
        between: 'justify-between',
        center: 'justify-center',
      },
    },
    defaultVariants: {
      justify: 'between',
    },
  });

  interface Props {
    loading?: boolean;
    loadingVariant?: 'spinner' | 'dots';
    justify?: 'between' | 'center';
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    loadingIcon?: Snippet<[]>;
    children?: Snippet<[]>;
  }

  let {
    loading = false,
    loadingVariant,
    justify = 'between',
    leftIcon,
    rightIcon,
    loadingIcon,
    children,
  }: Props = $props();
</script>

{#if !leftIcon && !rightIcon}
  {@render children?.()}
  <ButtonIcon {loading} {loadingVariant} {loadingIcon} />
{:else}
  <div class={contentVariants({ justify })}>
    {#if leftIcon}
      <ButtonIcon {loading} {loadingVariant} {loadingIcon}>
        {@render leftIcon()}
      </ButtonIcon>
    {/if}
    {@render children?.()}
    {#if rightIcon}
      <ButtonIcon {loading} {loadingVariant} {loadingIcon}>
        {@render rightIcon()}
      </ButtonIcon>
    {/if}
  </div>
{/if}
