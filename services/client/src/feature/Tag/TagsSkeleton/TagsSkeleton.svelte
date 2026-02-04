<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 8, class: customClass = '' }: Props = $props();

  const skeletonItems = $derived(Array(count).fill(null));
</script>

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
            <Skeleton width="40px" height="10px" />
          </th>
          <th class="h-10 px-4 text-center">
            <Skeleton width="50px" height="10px" class="mx-auto" />
          </th>
          <th class="h-10 px-4 text-center">
            <Skeleton width="55px" height="10px" class="mx-auto" />
          </th>
          <th class="h-10 px-4 text-right">
            <Skeleton width="50px" height="10px" class="ml-auto" />
          </th>
        </tr>
      </thead>
      <tbody>
        {#each skeletonItems as _, index}
          <tr
            class="border-b border-gray-100 dark:border-gray-700/50 last:border-b-0"
            style="animation-delay: {index * 75}ms"
          >
            <td class="h-14 px-4">
              <div class="flex items-center gap-3">
                <Skeleton width="24px" height="24px" rounded="md" />
                <div class="flex items-center gap-2">
                  {#if index % 3 !== 0}
                    <div class="flex items-center gap-0.5">
                      {#each Array(index % 3) as _}
                        <div
                          class="w-1.5 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"
                        ></div>
                      {/each}
                    </div>
                  {/if}
                  <Skeleton width="{80 + (index % 4) * 20}px" height="14px" />
                </div>
              </div>
            </td>
            <td class="h-14 px-4 text-center">
              <Skeleton
                width="32px"
                height="20px"
                rounded="md"
                class="mx-auto"
              />
            </td>
            <td class="h-14 px-4 text-center">
              <Skeleton
                width="32px"
                height="20px"
                rounded="md"
                class="mx-auto"
              />
            </td>
            <td class="h-14 px-4 text-right">
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
