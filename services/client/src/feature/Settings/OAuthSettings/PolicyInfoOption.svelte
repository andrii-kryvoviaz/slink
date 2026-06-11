<script lang="ts">
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import { getPolicyInfoContext } from './PolicyInfo.context';

  interface Props {
    value: string;
    icon: Snippet;
    label: Snippet;
    children: Snippet;
  }

  let { value, icon, label, children }: Props = $props();

  const context = getPolicyInfoContext();
  const isActive = $derived(context.value === value);

  const policyRow = cva(
    'flex items-start gap-3 rounded-lg p-2.5 transition-colors',
    {
      variants: {
        active: {
          true: 'bg-slate-100/70 dark:bg-slate-800/60',
          false: '',
        },
      },
      defaultVariants: { active: false },
    },
  );
</script>

<div class={policyRow({ active: isActive })}>
  <div class="flex-shrink-0 mt-0.5">
    {@render icon()}
  </div>
  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-1.5 mb-0.5">
      <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
        {@render label()}
      </span>
      {#if isActive}
        <span
          class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300"
        >
          Current
        </span>
      {/if}
    </div>
    <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
      {@render children()}
    </p>
  </div>
</div>
