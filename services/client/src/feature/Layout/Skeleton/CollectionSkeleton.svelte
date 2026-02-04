<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();
</script>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 {customClass}">
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
