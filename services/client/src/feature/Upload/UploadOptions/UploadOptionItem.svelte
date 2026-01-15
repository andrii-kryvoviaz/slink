<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  interface Props {
    icon?: string;
    label?: Snippet;
    description?: Snippet;
    children?: Snippet;
    action?: Snippet;
    disabled?: boolean;
  }

  let {
    icon = 'ph:circle',
    label,
    description,
    children,
    action,
    disabled = false,
  }: Props = $props();
</script>

<div
  class="group relative"
  class:opacity-50={disabled}
  class:pointer-events-none={disabled}
>
  <div class="flex items-start gap-3">
    <div
      class="w-8 h-8 rounded-lg bg-slate-100/70 dark:bg-slate-700/40 flex items-center justify-center shrink-0 border border-slate-200/50 dark:border-slate-600/30"
    >
      <Icon {icon} class="w-4 h-4 text-slate-600 dark:text-slate-400" />
    </div>

    <div class="flex-1 min-w-0 space-y-2">
      <div class="flex items-center justify-between gap-2">
        <div>
          {#if label}
            <span
              class="text-sm font-medium text-slate-700 dark:text-slate-200 block"
            >
              {@render label()}
            </span>
          {/if}
          {#if description}
            <span class="text-xs text-slate-500 dark:text-slate-400">
              {@render description()}
            </span>
          {/if}
        </div>
        {#if action}
          <div class="shrink-0">
            {@render action()}
          </div>
        {/if}
      </div>

      {#if children}
        <div>
          {@render children()}
        </div>
      {/if}
    </div>
  </div>
</div>
