<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    viewMode?: 'list' | 'grid';
    class?: string;
  }

  let {
    count = 6,
    viewMode = 'list',
    class: customClass = '',
  }: Props = $props();

  const skeletonItems = $derived(Array(count).fill(null));
</script>

{#if viewMode === 'grid'}
  <div
    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 auto-rows-fr {customClass}"
  >
    {#each skeletonItems as _, index}
      <div
        class="h-full bg-white dark:bg-gray-900/60 rounded-xl border border-gray-200 dark:border-gray-800 p-5"
        style="animation-delay: {index * 75}ms"
      >
        <div class="flex items-start space-x-4">
          <Skeleton width="48px" height="48px" rounded="full" />
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <div class="mb-3">
                  <Skeleton width="120px" height="18px" class="mb-2" />
                  <Skeleton width="160px" height="14px" />
                </div>
                <div class="flex items-center gap-2">
                  <Skeleton width="60px" height="22px" rounded="full" />
                  <Skeleton width="70px" height="22px" rounded="full" />
                </div>
              </div>
              <Skeleton width="32px" height="32px" rounded="md" class="ml-3" />
            </div>
          </div>
        </div>
      </div>
    {/each}
  </div>
{:else}
  <div
    class="rounded-xl border border-gray-200/60 dark:border-gray-700/40 bg-white dark:bg-gray-900/60 overflow-hidden {customClass}"
  >
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr
            class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700"
          >
            <th class="h-12 px-4 text-left">
              <Skeleton width="50px" height="12px" />
            </th>
            <th class="h-12 px-4 text-left">
              <Skeleton width="70px" height="12px" />
            </th>
            <th class="h-12 px-4 text-left min-w-30">
              <Skeleton width="50px" height="12px" />
            </th>
            <th class="h-12 px-4 text-left min-w-25">
              <Skeleton width="45px" height="12px" />
            </th>
            <th class="h-12 px-4 text-right w-12.5">
              <Skeleton width="55px" height="12px" class="ml-auto" />
            </th>
          </tr>
        </thead>
        <tbody>
          {#each skeletonItems as _, index}
            <tr
              class="border-b border-gray-100 dark:border-gray-700/50 last:border-b-0"
              style="animation-delay: {index * 75}ms"
            >
              <td class="h-16 px-4">
                <div class="flex items-center gap-3">
                  <Skeleton width="32px" height="32px" rounded="full" />
                  <div class="flex flex-col gap-1">
                    <Skeleton width="100px" height="14px" />
                    <Skeleton width="140px" height="12px" />
                  </div>
                </div>
              </td>
              <td class="h-16 px-4">
                <Skeleton width="80px" height="14px" />
              </td>
              <td class="h-16 px-4">
                <Skeleton width="80px" height="22px" rounded="full" />
              </td>
              <td class="h-16 px-4">
                <Skeleton width="60px" height="22px" rounded="full" />
              </td>
              <td class="h-16 px-4 text-right">
                <Skeleton
                  width="32px"
                  height="32px"
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
{/if}
