<script lang="ts">
  interface Props {
    count: number;
    type: 'images' | 'children';
  }

  let { count, type }: Props = $props();

  const colorClass =
    type === 'images'
      ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
      : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
</script>

{#if count > 0}
  <span
    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {colorClass}"
  >
    {count}
  </span>
{:else}
  <span class="text-gray-400">0</span>
{/if}
