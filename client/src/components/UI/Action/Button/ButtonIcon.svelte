<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    loadingIcon?: Snippet;
    children?: Snippet;
  }

  let { loading = false, loadingIcon, children }: Props = $props();
</script>

{#if loading}
  {#if loadingIcon}
    {@render loadingIcon?.()}
  {:else}
    <Icon
      icon="eos-icons:three-dots-loading"
      class="h-full w-full max-w-5 max-h-5"
    />
  {/if}
{:else}
  {@render children?.()}
{/if}
