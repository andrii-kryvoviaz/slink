<script lang="ts">
  import Icon from '@iconify/svelte';

  export let loading = false;
  export let customLoadingIcon: boolean = false;

  const defaultLoadingIcon = {
    icon: 'eos-icons:three-dots-loading',
    class: 'h-full w-full max-w-5 max-h-5',
  };
</script>

{#if loading}
  {#if customLoadingIcon}
    <slot name="loading" />
  {:else}
    <Icon {...defaultLoadingIcon} />
  {/if}
{:else}
  <slot />
{/if}
