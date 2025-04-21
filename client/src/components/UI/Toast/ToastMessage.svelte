<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    removeToast?: () => void;
    messageIcon?: Snippet;
    children?: Snippet;
  }

  let { removeToast = () => {}, messageIcon, children }: Props = $props();
</script>

<div class="flex items-center gap-2">
  {#if messageIcon}
    {@render messageIcon()}
  {:else}
    <Icon
      icon="clarity:info-standard-line"
      class="relative top-[-2px] mr-1 inline-block text-2xl text-blue-500"
    />
  {/if}
  <div class="w-full text-left text-sm font-normal">
    {#if children}
      {@render children()}
    {:else}
      Alert Message
    {/if}
  </div>
  <button
    type="button"
    class="m-0.5 ml-auto cursor-pointer whitespace-normal rounded-lg p-1.5 hover:bg-gray-100 focus:outline-hidden focus:ring-gray-400 dark:hover:bg-gray-600"
    aria-label="Close"
    onclick={removeToast}
    ><span class="sr-only">Close</span>
    <Icon icon="ic:round-close" /></button
  >
</div>
