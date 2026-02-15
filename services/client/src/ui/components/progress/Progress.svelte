<script lang="ts">
  import { cva } from 'class-variance-authority';

  import { className as cn } from '$lib/utils/ui/className';

  interface Props {
    value: number;
    max?: number;
    size?: 'sm' | 'md' | 'lg';
    variant?: 'default' | 'success' | 'error' | 'warning' | 'subtle';
    class?: string;
    showPercentage?: boolean;
  }

  let {
    value,
    max = 100,
    size = 'md',
    variant = 'default',
    class: className,
    showPercentage = false,
  }: Props = $props();

  const progressVariants = cva('w-full rounded-full overflow-hidden', {
    variants: {
      size: {
        sm: 'h-1',
        md: 'h-2',
        lg: 'h-3',
      },
      variant: {
        default: 'bg-gray-200 dark:bg-gray-700',
        success: 'bg-gray-200 dark:bg-gray-700',
        error: 'bg-gray-200 dark:bg-gray-700',
        warning: 'bg-gray-200 dark:bg-gray-700',
        subtle: 'bg-slate-200/60 dark:bg-slate-700/40',
      },
    },
    defaultVariants: {
      size: 'md',
      variant: 'default',
    },
  });

  const progressBarVariants = cva(
    'h-full transition-all duration-300 ease-out rounded-full',
    {
      variants: {
        variant: {
          default: 'bg-blue-500',
          success: 'bg-green-500',
          error: 'bg-red-500',
          warning: 'bg-yellow-500',
          subtle: 'bg-slate-600 dark:bg-slate-300',
        },
      },
      defaultVariants: {
        variant: 'default',
      },
    },
  );

  let percentage = $derived(Math.min(Math.max((value / max) * 100, 0), 100));
</script>

<div class={cn(progressVariants({ size, variant }), className)}>
  <div
    class={progressBarVariants({ variant })}
    style="width: {percentage}%"
    role="progressbar"
    aria-valuenow={value}
    aria-valuemin="0"
    aria-valuemax={max}
  ></div>
</div>

{#if showPercentage}
  <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">
    {percentage.toFixed(0)}%
  </div>
{/if}
