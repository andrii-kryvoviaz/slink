<script lang="ts">
  import Icon from '@iconify/svelte';

  export let loading = false;
  export let customLoadingIcon: boolean = false;

  const defaultLoadingIcon = {
    icon: 'eos-icons:three-dots-loading',
    class: 'h-5 w-5',
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
