<script lang="ts">
  import { type VariantProps, cva } from 'class-variance-authority';

  import Icon from '@iconify/svelte';
  import type { HTMLButtonAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

  const addButtonVariants = cva(
    'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full border border-dashed transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent',
    {
      variants: {
        variant: {
          indigo:
            'border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-indigo-400 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 disabled:hover:border-slate-300 disabled:hover:text-slate-500',
          emerald:
            'border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-emerald-400 dark:hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/30 disabled:hover:border-slate-300 disabled:hover:text-slate-500',
          blue: 'border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-indigo-400 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 disabled:hover:border-slate-300 disabled:hover:text-slate-500',
          violet:
            'border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-violet-400 dark:hover:border-violet-500 hover:text-violet-600 dark:hover:text-violet-400 hover:bg-violet-50/50 dark:hover:bg-violet-950/30 disabled:hover:border-slate-300 disabled:hover:text-slate-500',
          slate:
            'border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-slate-400 dark:hover:border-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/50 disabled:hover:border-slate-300 disabled:hover:text-slate-500',
        },
      },
      defaultVariants: {
        variant: 'indigo',
      },
    },
  );

  type AddButtonVariant = VariantProps<typeof addButtonVariants>['variant'];

  interface Props extends HTMLButtonAttributes {
    label: string;
    icon?: string;
    variant?: AddButtonVariant;
    disabled?: boolean;
  }

  let {
    label,
    icon,
    variant = 'slate',
    disabled = false,
    class: className,
    ...restProps
  }: Props = $props();
</script>

<button
  type="button"
  {disabled}
  class={cn(addButtonVariants({ variant }), className)}
  {...restProps}
>
  {#if icon}
    <Icon {icon} class="w-3.5 h-3.5" />
  {/if}
  <span>{label}</span>
  <Icon icon="ph:plus" class="w-3 h-3" />
</button>
