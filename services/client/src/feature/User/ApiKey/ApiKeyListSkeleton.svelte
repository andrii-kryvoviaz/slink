<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
  }

  let { count = 3 }: Props = $props();

  const skeletonItems = $derived(Array(count).fill(null));
</script>

<div class="space-y-3">
  {#each skeletonItems as _, index}
    <div
      class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900/60"
      style="animation-delay: {index * 75}ms"
    >
      <div class="flex items-center gap-4">
        <Skeleton width="40px" height="40px" rounded="lg" />
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2 mb-2">
            <Skeleton width="120px" height="16px" />
            <Skeleton width="60px" height="14px" />
          </div>
          <div class="flex items-center gap-2">
            <Skeleton width="80px" height="12px" />
            <Skeleton width="70px" height="12px" />
            <Skeleton width="70px" height="12px" />
          </div>
        </div>
        <Skeleton width="32px" height="32px" rounded="lg" />
      </div>
    </div>
  {/each}
</div>
