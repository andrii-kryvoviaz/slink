<script lang="ts">
  import { Skeleton } from '@slink/feature/Layout';

  interface Props {
    sections?: number;
    itemsPerSection?: number;
  }

  let { sections = 2, itemsPerSection = 3 }: Props = $props();
</script>

<div class="space-y-8">
  {#each Array(sections) as _}
    <div class="space-y-1">
      <div class="pb-3">
        <Skeleton width="120px" height="12px" />
      </div>

      <div
        class="rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 divide-y divide-gray-100 dark:divide-gray-800"
      >
        {#each Array(itemsPerSection) as _}
          <div class="flex items-center justify-between gap-6 px-4 py-4">
            <div class="flex-1 space-y-2">
              <Skeleton width="140px" height="14px" />
              <Skeleton width="200px" height="10px" />
            </div>
            <Skeleton width="44px" height="24px" rounded="full" />
          </div>
        {/each}
      </div>

      <div class="flex justify-end pt-4">
        <Skeleton width="64px" height="32px" rounded="lg" />
      </div>
    </div>
  {/each}
</div>
