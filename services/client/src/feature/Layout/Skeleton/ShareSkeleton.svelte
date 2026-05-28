<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 8, class: customClass = '' }: Props = $props();
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
          <th class="h-10 px-4 text-left sm:w-[320px]">
            <Skeleton width="40px" height="10px" />
          </th>
          <th class="h-10 px-4 text-left w-[160px]">
            <Skeleton width="60px" height="10px" />
          </th>
          <th class="h-10 px-4 text-left w-[140px]">
            <Skeleton width="70px" height="10px" />
          </th>
          <th class="h-10 px-4 text-right w-[80px]">
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
            <td class="h-16 px-4">
              <div class="flex items-center gap-3">
                <Skeleton width="40px" height="40px" rounded="md" />
                <div class="flex flex-col gap-1.5">
                  <Skeleton width="{140 + (index % 4) * 30}px" height="14px" />
                  <Skeleton width="{80 + (index % 3) * 20}px" height="10px" />
                </div>
              </div>
            </td>
            <td class="h-16 px-4">
              <Skeleton width="100px" height="14px" />
            </td>
            <td class="h-16 px-4">
              <div class="flex items-center gap-1.5">
                <Skeleton width="22px" height="22px" rounded="full" />
                {#if index % 2 === 0}
                  <Skeleton width="22px" height="22px" rounded="full" />
                {/if}
              </div>
            </td>
            <td class="h-16 px-4">
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
