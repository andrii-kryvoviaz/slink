<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    count?: number;
    class?: string;
  }

  let { count = 6, class: customClass = '' }: Props = $props();

  const skeletonItems = $derived(Array(count).fill(null));
</script>

<div class="flex flex-col gap-3 {customClass}">
  {#each skeletonItems as _, index}
    <div
      class="flex items-start gap-4 p-4 pl-5 rounded-2xl bg-gray-50 dark:bg-white/2"
      style="animation-delay: {index * 75}ms"
    >
      <Skeleton width="40px" height="40px" rounded="xl" />

      <div class="flex-1 space-y-2">
        <div class="flex items-baseline gap-2">
          <Skeleton width="100px" height="18px" rounded="md" />
          <Skeleton width="60px" height="14px" rounded="md" />
        </div>
        <Skeleton width="85%" height="14px" rounded="md" />
        <Skeleton width="50px" height="12px" rounded="md" />
      </div>
    </div>
  {/each}
</div>
