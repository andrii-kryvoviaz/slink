<script lang="ts">
  import { Shortcut } from '@slink/ui/components';
  import { cardTitleTheme } from '@slink/ui/components/card';
  import { useDropzone } from '@slink/ui/components/dropzone';

  import Icon from '@iconify/svelte';

  const dropzone = useDropzone();

  const allowMultiple = $derived(dropzone.multiple);
</script>

<div
  class="flex flex-col items-center justify-center p-4 sm:p-8 text-center relative z-10"
>
  <div class="mb-6 sm:mb-8 relative">
    <div
      class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-linear-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/15 dark:to-purple-500/15 flex items-center justify-center border border-indigo-200/40 dark:border-purple-400/20 transition-all duration-300 backdrop-blur-sm"
    >
      <Icon
        icon="ph:upload-simple"
        class="h-10 w-10 sm:h-12 sm:w-12 text-indigo-600 dark:text-purple-400 transition-all duration-300"
      />
      <div
        class="absolute -top-1 -right-1 sm:-top-1.5 sm:-right-1.5 w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 dark:from-emerald-400 dark:to-green-500 flex items-center justify-center shadow-md shadow-green-500/20 dark:shadow-green-600/25 border-2 border-white dark:border-slate-800 backdrop-blur-sm"
      >
        <Icon
          icon="ph:plus-bold"
          class="h-2.5 w-2.5 sm:h-3 sm:w-3 text-white drop-shadow-sm"
        />
      </div>
    </div>
  </div>

  <div class="mb-4 sm:mb-6 space-y-2">
    <h2 class={cardTitleTheme()}>
      {#if allowMultiple}Drop your images here{:else}Drop your image here{/if}
    </h2>
    <p
      class="text-slate-500 dark:text-slate-400 text-base sm:text-lg font-light max-w-xs sm:max-w-md mx-auto"
    >
      or click to browse from your device
    </p>
  </div>

  <div class="mb-6 sm:mb-8 group/shortcut hidden sm:block">
    <div
      class="flex items-center gap-4 pl-4 pr-3 py-2 rounded-xl bg-slate-100/70 dark:bg-slate-700/40 border border-slate-200/50 dark:border-slate-600/30 backdrop-blur-sm group-hover/shortcut:bg-slate-200/80 dark:group-hover/shortcut:bg-slate-600/50 transition-all duration-200"
    >
      <span
        class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
        >Quick paste</span
      >
      <span
        class="h-5 w-px bg-slate-300/70 dark:bg-slate-600/60"
        aria-hidden="true"
      ></span>
      <Shortcut control={true} key="v" size="lg" />
    </div>
  </div>

  <div class="space-y-3 sm:space-y-4">
    <div class="flex flex-wrap justify-center gap-2 sm:gap-2.5 mb-3 sm:mb-4">
      {#each ['png', 'jpg', 'gif', 'svg', 'webp', 'heic'] as format}
        <span
          class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium uppercase tracking-wider bg-slate-100/70 dark:bg-slate-700/40 text-slate-600 dark:text-slate-400 rounded-lg border border-slate-200/50 dark:border-slate-600/30 backdrop-blur-sm"
        >
          {format}
        </span>
      {/each}
    </div>

    <div class="text-center">
      <a
        href="/help/faq#supported-image-formats"
        class="inline-flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-200 font-medium group/link"
        onclick={(event) => event.stopPropagation()}
      >
        <Icon icon="ph:info-circle" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
        <span>View all supported formats</span>
        <Icon
          icon="ph:arrow-right"
          class="h-3.5 w-3.5 sm:h-4 sm:w-4 transition-transform duration-200 group-hover/link:translate-x-0.5"
        />
      </a>
    </div>
  </div>
</div>
