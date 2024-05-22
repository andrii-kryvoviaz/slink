<script lang="ts">
  import Icon from '@iconify/svelte';

  export let danger = false;
  export let disabled = false;
  export let loading = false;
</script>

<button class="tooltip-item" class:danger on:click {disabled}>
  {#if loading}
    <Icon icon="mingcute:loading-line" class="animate-spin" />
  {:else}
    <slot name="icon" />
  {/if}
  <span>
    <slot />
  </span>
</button>

<style>
  .tooltip-item {
    @apply flex cursor-pointer items-center gap-2 rounded-md p-1 px-3;
    @apply text-gray-50;
  }

  .tooltip-item[disabled] {
    @apply cursor-not-allowed text-gray-500;
  }

  .tooltip-item:hover {
    @apply bg-gray-500 text-gray-50;
  }

  .danger:hover {
    @apply bg-red-500 text-gray-50;
  }

  .tooltip-item[disabled]:hover {
    @apply bg-inherit text-gray-500;
  }
</style>
