<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();

  const skeletonItems = $derived(Array(count).fill(null));
</script>

<div class="columns-1 md:columns-2 xl:columns-3 gap-4 {customClass}">
  {#each skeletonItems as _, index}
    <div
      class="break-inside-avoid rounded-xl overflow-hidden mb-4 bg-white dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50"
      style="animation-delay: {index * 100}ms"
    >
      <div class="relative">
        <Skeleton
          width="100%"
          height="{200 + (index % 3) * 100}px"
          rounded="none"
        />
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
