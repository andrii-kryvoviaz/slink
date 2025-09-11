<script lang="ts">
  import { cva } from 'class-variance-authority';

  import { cn } from '@slink/utils/ui';

  interface Props {
    visible?: boolean;
    variant?: 'default' | 'accent';
  }

  let { visible = true, variant = 'default' }: Props = $props();

  const ribbonVariants = cva(
    'relative transform rotate-45 text-center font-medium py-1 right-3 top-3 w-32 transition-colors',
    {
      variants: {
        variant: {
          default: 'bg-slate-900 dark:bg-slate-100',
          accent: 'bg-blue-600 dark:bg-blue-500',
        },
      },
      defaultVariants: {
        variant: 'default',
      },
    },
  );

  const textVariants = cva(
    'text-xs font-semibold tracking-wide select-none whitespace-nowrap',
    {
      variants: {
        variant: {
          default: 'text-white dark:text-slate-900',
          accent: 'text-white',
        },
      },
      defaultVariants: {
        variant: 'default',
      },
    },
  );
</script>

{#if visible}
  <div
    class="overflow-hidden absolute top-0 right-0 size-20 pointer-events-none z-10"
  >
    <div class={cn(ribbonVariants({ variant }))}>
      <span class={cn(textVariants({ variant }))}> DEMO </span>
    </div>
  </div>
{/if}
