<script lang="ts">
  import { exploreListRowTheme } from '@slink/feature/Image/Explore/ExploreView.theme';
  import { Skeleton } from '@slink/feature/Layout';

  import type { ViewMode } from '@slink/lib/settings';
  import { getSkeletonHeight } from '@slink/lib/utils/ui/skeletonHeight';

  interface Props {
    count?: number;
    viewMode?: ViewMode;
    class?: string;
  }

  let {
    count = 6,
    viewMode = 'grid',
    class: customClass = '',
  }: Props = $props();

  const rowTheme = exploreListRowTheme();
</script>

{#if viewMode === 'list'}
  <ul class="@container flex flex-col gap-3 {customClass}">
    {#each Array(count) as _, index}
      <li
        class={rowTheme.skeletonRow()}
        style="animation-delay: {index * 100}ms"
      >
        <div class={rowTheme.rail()}>
          <div class={rowTheme.frame()}>
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
        </div>

        <div class={rowTheme.body()}>
          <div class="flex items-center gap-2.5">
            <Skeleton width="24px" height="24px" rounded="full" />
            <Skeleton width="100px" height="14px" />
          </div>

          <Skeleton width="70%" height="12px" />

          <Skeleton width="180px" height="12px" />

          <div class="flex gap-2">
            <Skeleton width="50px" height="22px" rounded="full" />
            <Skeleton width="40px" height="22px" rounded="full" />
          </div>
        </div>
      </li>
    {/each}
  </ul>
{:else}
  <div class="columns-1 md:columns-2 xl:columns-3 gap-4 {customClass}">
    {#each Array(count) as _, index}
      <div
        class="break-inside-avoid rounded-xl overflow-hidden mb-4 bg-card dark:bg-card/80 backdrop-blur-sm border border-border/50"
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
            <div class="mt-2.5 pt-2.5 border-t border-border">
              <Skeleton width="100%" height="12px" class="mb-1" />
              <Skeleton width="75%" height="12px" />
            </div>
          {/if}
        </div>
      </div>
    {/each}
  </div>
{/if}
