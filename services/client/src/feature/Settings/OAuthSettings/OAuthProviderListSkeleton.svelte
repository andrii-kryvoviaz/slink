<script lang="ts">
  interface Props {
    items?: number;
  }

  let { items = 3 }: Props = $props();
</script>

<div
  class="animate-pulse divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
>
  {#each { length: items } as _}
    <div class="flex items-center justify-between gap-4 px-4 py-3.5">
      <div class="flex items-center gap-3 min-w-0">
        <div
          class="w-9 h-9 rounded-lg bg-gray-200 dark:bg-gray-700 shrink-0"
        ></div>

        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-700"></div>
            <div class="h-3 w-14 rounded bg-gray-200 dark:bg-gray-700"></div>
          </div>
          <div
            class="mt-1.5 h-3 w-48 rounded bg-gray-200 dark:bg-gray-700"
          ></div>
        </div>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <div class="w-8 h-5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700"></div>
      </div>
    </div>
  {/each}
</div>
