<script lang="ts">
  import { Skeleton } from '@slink/ui/components/skeleton';

  import { cn } from '@slink/utils/ui';

  const skeletonClass = 'bg-white/10';
</script>

<div
  class="flex flex-col w-full h-full bg-white/5 backdrop-blur-sm rounded-2xl p-4"
>
  <div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-medium text-white/80">Comments</h3>
    <span class="text-xs text-white/40">Coming soon</span>
  </div>

  <div class="flex-1 space-y-4 overflow-hidden">
    {#each Array(5) as _, i}
      <div class={cn('flex gap-3', i > 2 && 'opacity-50')}>
        <Skeleton class={cn('w-8 h-8 rounded-full shrink-0', skeletonClass)} />
        <div class="flex-1 space-y-2">
          <Skeleton class={cn('h-3 w-24', skeletonClass)} />
          <Skeleton class={cn('h-3 w-full', skeletonClass)} />
          <Skeleton class={cn('h-3 w-3/4', skeletonClass)} />
        </div>
      </div>
    {/each}
  </div>

  <div class="mt-4 pt-4 border-t border-white/10">
    <div class="flex items-center gap-3">
      <Skeleton class={cn('w-8 h-8 rounded-full shrink-0', skeletonClass)} />
      <Skeleton class={cn('flex-1 h-10 rounded-full', skeletonClass)} />
    </div>
  </div>
</div>
