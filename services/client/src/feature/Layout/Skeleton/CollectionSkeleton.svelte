<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  import type { ViewMode } from '@slink/lib/settings';

  interface Props {
    count?: number;
    viewMode?: ViewMode;
    class?: string;
  }

  let {
    count = 6,
    viewMode = 'grid',
    class: customClass = '',
  }: Props = $props();
</script>

{#if viewMode === 'table'}
  <div
    class="rounded-xl border border-gray-200/60 dark:border-gray-700/40 bg-white dark:bg-gray-900/60 overflow-hidden {customClass}"
  >
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr
            class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700"
          >
            <th class="h-10 px-4 text-left">
              <Skeleton width="60px" height="10px" />
            </th>
            <th class="h-10 px-4 text-center">
              <Skeleton width="30px" height="10px" class="mx-auto" />
            </th>
            <th class="h-10 px-4 text-left">
              <Skeleton width="80px" height="10px" />
            </th>
            <th class="h-10 px-4 text-left">
              <Skeleton width="60px" height="10px" />
            </th>
            <th class="h-10 px-4 text-right">
              <Skeleton width="50px" height="10px" class="ml-auto" />
            </th>
          </tr>
        </thead>
        <tbody>
          {#each Array(count) as _, index}
            <tr
              class="border-b border-gray-100 dark:border-gray-700/50 last:border-b-0"
              style="animation-delay: {index * 75}ms"
            >
              <td class="h-14 px-4">
                <div class="flex items-center gap-3">
                  <Skeleton width="40px" height="40px" rounded="md" />
                  <Skeleton width="{100 + (index % 4) * 25}px" height="14px" />
                </div>
              </td>
              <td class="h-14 px-4">
                <Skeleton
                  width="24px"
                  height="20px"
                  rounded="md"
                  class="mx-auto"
                />
              </td>
              <td class="h-14 px-4">
                <Skeleton width="{120 + (index % 3) * 40}px" height="14px" />
              </td>
              <td class="h-14 px-4">
                <Skeleton width="80px" height="14px" />
              </td>
              <td class="h-14 px-4">
                <Skeleton
                  width="36px"
                  height="36px"
                  rounded="md"
                  class="ml-auto"
                />
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
{:else}
  <div
    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 {customClass}"
  >
    {#each Array(count) as _, index}
      <div
        class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60"
        style="animation-delay: {index * 100}ms"
      >
        <div class="aspect-4/3 relative bg-gray-100 dark:bg-gray-800/50">
          <Skeleton width="100%" height="100%" rounded="none" />
          <div class="absolute bottom-2 left-2">
            <Skeleton
              width="50px"
              height="24px"
              rounded="full"
              class="opacity-60"
            />
          </div>
        </div>

        <div class="p-3 flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <Skeleton width="70%" height="16px" class="mb-2" />
            {#if index % 2 === 0}
              <Skeleton width="100%" height="12px" class="mb-1" />
              <Skeleton width="60%" height="12px" class="mb-2" />
            {/if}
            <Skeleton width="80px" height="10px" class="mt-2" />
          </div>
          <Skeleton width="28px" height="28px" rounded="md" class="shrink-0" />
        </div>
      </div>
    {/each}
  </div>
{/if}
