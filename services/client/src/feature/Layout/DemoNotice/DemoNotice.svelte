<script lang="ts">
  import AlertTriangleIcon from '@lucide/svelte/icons/alert-triangle';
  import ClockIcon from '@lucide/svelte/icons/clock';
  import { cva } from 'class-variance-authority';

  import { cn } from '@slink/utils/ui';

  let { resetIntervalMinutes = 120, visible = true } = $props();

  function formatInterval(minutes: number): string {
    if (minutes < 60) {
      return `${minutes} minute${minutes !== 1 ? 's' : ''}`;
    }

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (remainingMinutes === 0) {
      return `${hours} hour${hours !== 1 ? 's' : ''}`;
    }

    return `${hours} hour${hours !== 1 ? 's' : ''} and ${remainingMinutes} minute${remainingMinutes !== 1 ? 's' : ''}`;
  }

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
    <ClockIcon class="h-4 w-4 flex-shrink-0" />
    <div class="flex flex-wrap items-center justify-center gap-1 text-center">
      <span>Demo environment resets every</span>
      <strong>{formatInterval(resetIntervalMinutes)}</strong>
      <span class="flex items-center gap-1">
        <AlertTriangleIcon class="h-3 w-3" />
        Current session may be destroyed
      </span>
    </div>
  </div>
{/if}
