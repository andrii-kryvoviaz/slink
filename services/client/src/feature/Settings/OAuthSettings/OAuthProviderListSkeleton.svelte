<script lang="ts">
  interface Props {
    items?: number;
  }

  let { items = 3 }: Props = $props();
</script>

<div
  class="animate-pulse divide-y divide-muted rounded-xl bg-muted-subtle/50 dark:bg-muted-subtle/30 border border-muted overflow-hidden"
>
  {#each { length: items } as _}
    <div class="flex items-center justify-between gap-4 px-4 py-3.5">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-9 h-9 rounded-lg bg-muted shrink-0"></div>

        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <div class="h-4 w-24 rounded bg-muted"></div>
            <div class="h-3 w-14 rounded bg-muted"></div>
          </div>
          <div class="mt-1.5 h-3 w-48 rounded bg-muted"></div>
        </div>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <div class="w-8 h-5 rounded-full bg-muted"></div>
        <div class="w-4 h-4 rounded bg-muted"></div>
      </div>
    </div>
  {/each}
</div>
