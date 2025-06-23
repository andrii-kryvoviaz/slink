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

<div class="flex items-center gap-3">
  {#if messageIcon}
    {@render messageIcon()}
  {:else}
    <Icon
      icon="clarity:info-standard-line"
      class="relative mr-1 inline-block text-2xl text-blue-500 shrink-0"
    />
  {/if}
  <div class="w-full text-left text-sm font-medium leading-relaxed">
    {#if children}
      {@render children()}
    {:else}
      Alert Message
    {/if}
  </div>
  <button
    type="button"
    class="ml-auto cursor-pointer whitespace-normal rounded-xl p-2 hover:bg-gray-100/80 focus:outline-none focus:ring-2 focus:ring-gray-400/20 dark:hover:bg-gray-600/50 dark:focus:ring-gray-500/30 transition-all duration-200 shrink-0"
    aria-label="Close"
    onclick={removeToast}
    ><span class="sr-only">Close</span>
    <Icon icon="ic:round-close" class="h-4 w-4" /></button
  >
</div>
