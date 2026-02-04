<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  import { getSkeletonHeight } from '@slink/lib/utils/ui/skeletonHeight';

  interface Props {
    count?: number;
    viewMode?: 'list' | 'grid';
    class?: string;
  }

  let {
    count = 4,
    viewMode = 'list',
    class: customClass = '',
  }: Props = $props();
</script>

{#if viewMode === 'list'}
  <ul class="flex flex-col gap-3 {customClass}">
    {#each Array(count) as _, index}
      <li
        class="flex flex-col sm:flex-row w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60"
        style="animation-delay: {index * 100}ms"
      >
        <div
          class="relative w-full sm:w-40 md:w-48 lg:w-56 shrink-0 bg-gray-100 dark:bg-gray-800/80"
        >
          <div class="aspect-4/3 sm:aspect-square">
            <Skeleton width="100%" height="100%" rounded="none" />
          </div>
          <div class="absolute bottom-2 left-2">
            <Skeleton
              width="70px"
              height="24px"
              rounded="full"
              class="opacity-60"
            />
          </div>
          <div class="absolute bottom-2 right-2">
            <Skeleton
              width="45px"
              height="24px"
              rounded="full"
              class="opacity-60"
            />
          </div>
        </div>

        <div class="flex flex-col flex-1 p-3 sm:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3 mb-2">
            <Skeleton width="60%" height="20px" />
            <div class="flex gap-1">
              <Skeleton width="28px" height="28px" rounded="md" />
              <Skeleton width="28px" height="28px" rounded="md" />
              <Skeleton width="28px" height="28px" rounded="md" />
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 mb-3">
            <Skeleton width="50px" height="14px" />
            <Skeleton width="70px" height="14px" />
            <Skeleton width="45px" height="14px" />
            <Skeleton width="80px" height="14px" />
          </div>

          <div class="mt-auto flex gap-2">
            <Skeleton width="50px" height="22px" rounded="full" />
            <Skeleton width="40px" height="22px" rounded="full" />
          </div>
        </div>
      </li>
    {/each}
  </ul>
{:else}
  <div
    class="columns-1 sm:columns-2 md:columns-2 lg:columns-3 xl:columns-4 gap-4 {customClass}"
  >
    {#each Array(count) as _, index}
      <div
        class="break-inside-avoid mb-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60"
        style="animation-delay: {index * 80}ms"
      >
        <div class="relative">
          <Skeleton
            width="100%"
            height="{getSkeletonHeight(index)}px"
            rounded="none"
          />
          <div class="absolute top-2 left-2">
            <Skeleton
              width="60px"
              height="22px"
              rounded="full"
              class="opacity-60"
            />
          </div>
          <div class="absolute bottom-2 left-2">
            <Skeleton
              width="70px"
              height="24px"
              rounded="full"
              class="opacity-60"
            />
          </div>
          <div class="absolute bottom-2 right-2">
            <Skeleton
              width="40px"
              height="24px"
              rounded="full"
              class="opacity-60"
            />
          </div>
        </div>

        <div class="p-3">
          <div class="mb-2">
            <Skeleton width="80%" height="16px" />
          </div>

          <div class="flex flex-wrap items-center gap-1.5 mb-3">
            <Skeleton width="40px" height="12px" />
            <Skeleton width="55px" height="12px" />
            <Skeleton width="35px" height="12px" />
          </div>

          <div class="flex gap-1.5">
            <Skeleton width="45px" height="20px" rounded="full" />
            <Skeleton width="35px" height="20px" rounded="full" />
          </div>
        </div>
      </div>
    {/each}
  </div>
{/if}
