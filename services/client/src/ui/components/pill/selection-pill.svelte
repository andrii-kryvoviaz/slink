<script lang="ts" context="module">
  import { type VariantProps, cva } from 'class-variance-authority';

  const pillVariants = cva(
    'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed group',
    {
      variants: {
        variant: {
          indigo:
            'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-800/50',
          blue: 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800/50',
          emerald:
            'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-800/50',
          amber:
            'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 hover:bg-amber-200 dark:hover:bg-amber-800/50',
          rose: 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 hover:bg-rose-200 dark:hover:bg-rose-800/50',
          violet:
            'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 hover:bg-violet-200 dark:hover:bg-violet-800/50',
          slate:
            'bg-slate-100 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700/60',
        },
      },
      defaultVariants: {
        variant: 'indigo',
      },
    },
  );

  export type PillVariant = VariantProps<typeof pillVariants>['variant'];
</script>

<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    label: string;
    icon?: string;
    variant?: PillVariant;
    disabled?: boolean;
    onRemove?: () => void;
  }

  let {
    label,
    icon,
    variant = 'indigo',
    disabled = false,
    onRemove,
  }: Props = $props();
</script>

<button
  type="button"
  onclick={onRemove}
  {disabled}
  class={pillVariants({ variant })}
>
  {#if icon}
    <Icon {icon} class="w-3.5 h-3.5" />
  {/if}
  <span class="font-medium">{label}</span>
  <Icon
    icon="ph:x"
    class="w-3 h-3 opacity-50 group-hover:opacity-100 transition-opacity"
  />
</button>
