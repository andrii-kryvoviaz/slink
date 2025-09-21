<script lang="ts">
  import type { Snippet } from 'svelte';

  import ButtonIcon from './button-icon.svelte';

  interface Props {
    loading?: boolean;
    leftIcon?: Snippet<[]>;
    rightIcon?: Snippet<[]>;
    loadingIcon?: Snippet<[]>;
    children?: Snippet<[]>;
  }

  let {
    loading = false,
    leftIcon,
    rightIcon,
    loadingIcon,
    children,
  }: Props = $props();
</script>

{#if !leftIcon && !rightIcon}
  {@render children?.()}
  <ButtonIcon {loading} {loadingIcon} />
{:else}
  <div class="flex w-full items-center justify-between gap-2">
    {#if leftIcon}
      <ButtonIcon {loading} {loadingIcon}>
        {@render leftIcon()}
      </ButtonIcon>
    {/if}
    {@render children?.()}
    {#if rightIcon}
      <ButtonIcon {loading} {loadingIcon}>
        {@render rightIcon()}
      </ButtonIcon>
    {/if}
  </div>
{/if}
