<script lang="ts">
  import { Skeleton } from '@slink/components/UI/Skeleton';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();

  const skeletonItems = Array(count).fill(null);
</script>

<div class="columns-1 md:columns-2 xl:columns-3 gap-5 {customClass}">
  {#each skeletonItems as _, index}
    <div
      class="break-inside-avoid bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden mb-5"
      style="animation-delay: {index * 100}ms"
    >
      <div class="p-5 pb-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <Skeleton width="32px" height="32px" rounded="full" />
            <div class="flex-1 min-w-0">
              <Skeleton width="120px" height="16px" class="mb-1" />
              <Skeleton width="80px" height="12px" />
            </div>
          </div>
        </div>
      </div>

      <div class="relative">
        <Skeleton
          width="100%"
          height="{200 + (index % 3) * 100}px"
          rounded="none"
        />
      </div>

      <div class="flex flex-col justify-between gap-2 py-2 px-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="flex items-center">
              <Skeleton width="16px" height="16px" class="mr-1" rounded="sm" />
              <Skeleton width="30px" height="12px" />
            </div>
            <div class="flex items-center">
              <Skeleton width="16px" height="16px" class="mr-1" rounded="sm" />
              <Skeleton width="60px" height="12px" />
            </div>
          </div>
        </div>

        {#if index % 3 === 0}
          <div class="mt-2">
            <Skeleton width="100%" height="14px" class="mb-1" />
            <Skeleton width="75%" height="14px" />
          </div>
        {/if}
      </div>
    </div>
  {/each}
</div>
