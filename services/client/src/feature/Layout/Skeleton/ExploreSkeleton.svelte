<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  import { getSkeletonHeight } from '@slink/lib/utils/ui/skeletonHeight';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();
</script>

<div class="columns-1 md:columns-2 xl:columns-3 gap-4 {customClass}">
  {#each Array(count) as _, index}
    <div
      class="break-inside-avoid rounded-xl overflow-hidden mb-4 bg-white dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
      style="animation-delay: {index * 80}ms"
    >
      <div class="relative">
        <Skeleton
          width="100%"
          height="{getSkeletonHeight(index)}px"
          rounded="none"
        />
        <div class="absolute bottom-2 left-2">
          <Skeleton
            width="70px"
            height="24px"
            rounded="full"
            class="opacity-60"
          />
        </div>
      </div>

      <div class="p-3">
        <div class="flex items-center gap-2.5">
          <Skeleton width="28px" height="28px" rounded="full" />
          <div class="flex-1 min-w-0">
            <Skeleton width="100px" height="14px" class="mb-1" />
            <Skeleton width="60px" height="10px" />
          </div>
        </div>

        {#if index % 3 === 0}
          <div
            class="mt-2.5 pt-2.5 border-t border-gray-100 dark:border-gray-800"
          >
            <Skeleton width="100%" height="12px" class="mb-1" />
            <Skeleton width="75%" height="12px" />
          </div>
        {/if}
      </div>
    </div>
  {/each}
</div>
