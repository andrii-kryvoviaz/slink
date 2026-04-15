<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    loadingVariant?: 'spinner' | 'dots';
    loadingIcon?: Snippet<[]>;
    children?: Snippet<[]>;
  }

  let {
    loading = false,
    loadingVariant = 'spinner',
    loadingIcon,
    children,
  }: Props = $props();
</script>

{#if loading}
  {#if loadingIcon}
    {@render loadingIcon()}
  {:else if loadingVariant === 'dots'}
    <Icon
      icon="eos-icons:three-dots-loading"
      class="h-full w-full max-w-5 max-h-5"
    />
  {:else}
    <Icon
      icon="lucide:loader-2"
      class="h-full w-full max-w-5 max-h-5 animate-spin"
    />
  {/if}
{:else if children}
  {@render children()}
{/if}
