<script lang="ts">
  import type { Snippet } from 'svelte';
  import { twMerge } from 'tailwind-merge';

  import Icon from '@iconify/svelte';
  import { fade, scale } from 'svelte/transition';

  interface Props {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    children?: Snippet;
    class?: string;
  }

  let {
    open = $bindable(false),
    onOpenChange,
    title,
    description,
    size = 'md',
    children,
    class: className,
  }: Props = $props();

  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
  };

  function handleClose() {
    open = false;
    onOpenChange?.(false);
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
      handleClose();
    }
  }

  function handleBackdropClick(e: MouseEvent) {
    if (e.target === e.currentTarget) {
      handleClose();
    }
  }
</script>

{#if open}
  <div
    class="fixed inset-0 z-50 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    onclick={handleBackdropClick}
    onkeydown={handleKeydown}
    tabindex="-1"
  >
    <div
      class="fixed inset-0 bg-black/50 backdrop-blur-sm"
      role="button"
      tabindex="0"
      onclick={handleBackdropClick}
      onkeydown={handleKeydown}
      transition:fade={{ duration: 150 }}
    ></div>

    <div
      class={twMerge(
        'relative w-full bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 mx-4',
        sizeClasses[size],
        className,
      )}
      transition:scale={{ duration: 150, start: 0.95 }}
      onclick={(e) => e.stopPropagation()}
      onkeydown={(e) => {
        if (e.key === 'Enter' || e.key === ' ') e.stopPropagation();
      }}
      role="dialog"
      tabindex="0"
    >
      {#if title || description}
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
          {#if title}
            <h2
              class="text-lg font-semibold text-slate-900 dark:text-slate-100"
            >
              {title}
            </h2>
          {/if}
          {#if description}
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
              {description}
            </p>
          {/if}
        </div>
      {/if}

      <div class="px-6 py-4">
        {#if children}
          {@render children()}
        {/if}
      </div>

      <button
        class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 hover:text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        onclick={handleClose}
      >
        <Icon icon="ph:x" class="h-4 w-4" />
        <span class="sr-only">Close</span>
      </button>
    </div>
  </div>
{/if}
