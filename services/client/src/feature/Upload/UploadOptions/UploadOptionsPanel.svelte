<script lang="ts">
  import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
  } from '@slink/ui/components/collapsible';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { slide } from 'svelte/transition';

  interface Props {
    open?: boolean;
    disabled?: boolean;
    children?: Snippet;
  }

  let { open = $bindable(false), disabled = false, children }: Props = $props();
</script>

<Collapsible bind:open {disabled}>
  <div
    class="rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/50 shadow-lg shadow-slate-500/5 dark:shadow-black/10 backdrop-blur-sm transition-all duration-200"
    class:opacity-60={disabled}
    class:cursor-not-allowed={disabled}
  >
    <CollapsibleTrigger
      class="w-full px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-100/50 dark:hover:bg-slate-700/30 transition-colors duration-200 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
      {disabled}
    >
      <div class="flex items-center gap-3">
        <div
          class="w-8 h-8 rounded-lg bg-linear-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/15 dark:to-purple-500/15 flex items-center justify-center border border-indigo-200/40 dark:border-purple-400/20"
        >
          <Icon
            icon="ph:sliders-horizontal"
            class="w-4 h-4 text-indigo-600 dark:text-purple-400"
          />
        </div>
        <div class="text-left">
          <span
            class="text-sm font-medium text-slate-700 dark:text-slate-200 leading-tight block"
          >
            Upload Options
          </span>
          <span class="text-xs text-slate-500 dark:text-slate-400">
            Add tags to your upload
          </span>
        </div>
      </div>

      <div
        class="w-6 h-6 rounded-full bg-slate-100/70 dark:bg-slate-700/40 flex items-center justify-center transition-transform duration-200"
        class:rotate-180={open}
      >
        <Icon
          icon="ph:caret-down"
          class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400"
        />
      </div>
    </CollapsibleTrigger>

    <CollapsibleContent>
      {#if open}
        <div
          transition:slide={{ duration: 200 }}
          class="px-4 pb-4 pt-1 space-y-4"
        >
          <div
            class="h-px bg-linear-to-r from-transparent via-slate-200 dark:via-slate-700 to-transparent"
          ></div>
          {@render children?.()}
        </div>
      {/if}
    </CollapsibleContent>
  </div>
</Collapsible>
