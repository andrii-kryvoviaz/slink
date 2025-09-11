<script lang="ts">
  import AlertTriangleIcon from '@lucide/svelte/icons/alert-triangle';
  import { cva } from 'class-variance-authority';

  import { cn } from '@slink/utils/ui';

  let { visible = true } = $props();

  const bannerVariants = cva(
    'w-full flex items-center justify-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-300 border-b',
    {
      variants: {
        variant: {
          warning:
            'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950/50 dark:border-amber-800 dark:text-amber-200',
        },
      },
      defaultVariants: {
        variant: 'warning',
      },
    },
  );
</script>

{#if visible}
  <div class={cn(bannerVariants({ variant: 'warning' }))}>
    <AlertTriangleIcon class="h-4 w-4 flex-shrink-0" />
    <div class="flex flex-wrap items-center justify-center gap-1 text-center">
      <span>Demo instance - uploads and state changes are disabled</span>
    </div>
  </div>
{/if}
