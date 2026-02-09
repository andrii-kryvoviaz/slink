<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    children?: Snippet;
    onclick?: () => void;
  }

  let { children, onclick }: Props = $props();
</script>

{#snippet defaultLabel()}
  New item
{/snippet}

<div
  class="px-2 py-1.5 border-t border-gray-100/60 dark:border-gray-800/30 shrink-0"
>
  <button
    type="button"
    {onclick}
    class="flex items-center gap-2 px-2 py-1.5 rounded-md text-[13px] text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors w-full"
  >
    <Icon icon="ph:plus" class="w-3.5 h-3.5" />
    <span>{@render (children ?? defaultLabel)()}</span>
  </button>
</div>
