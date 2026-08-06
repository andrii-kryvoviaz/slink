<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    caption?: Snippet;
  }

  let { caption }: Props = $props();

  const ghostRows = [
    { width: 'w-[56%]', opacity: 'opacity-100' },
    { width: 'w-[40%]', opacity: 'opacity-65' },
    { width: 'w-[48%]', opacity: 'opacity-35' },
  ];
</script>

{#snippet defaultCaption()}
  Your items will appear here
{/snippet}

<div class="px-3 pt-3 pb-2.5">
  <div class="relative" aria-hidden="true">
    <div class="flex flex-col gap-2">
      {#each ghostRows as row (row.width)}
        <div class="flex items-center gap-2.5 {row.opacity}">
          <span class="bg-muted h-6 w-6 shrink-0 rounded-md"></span>
          <span class="bg-muted h-2.5 rounded-full {row.width}"></span>
        </div>
      {/each}
    </div>
    <div
      class="to-card pointer-events-none absolute inset-x-0 bottom-0 h-8 bg-gradient-to-b from-transparent"
    ></div>
  </div>
  <p class="text-foreground-subtle mt-2.5 text-center text-xs">
    {@render (caption ?? defaultCaption)()}
  </p>
</div>
