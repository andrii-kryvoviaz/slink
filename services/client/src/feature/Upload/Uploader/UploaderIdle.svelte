<script lang="ts">
  import { cardTitleTheme } from '@slink/ui/components/card';
  import { useDropzone } from '@slink/ui/components/dropzone';

  import Icon from '@iconify/svelte';

  const dropzone = useDropzone();

  const allowMultiple = $derived(dropzone.multiple);
</script>

<div class="relative z-10 flex flex-col items-center pt-2 sm:pt-3">
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

  <div class="space-y-2">
    <h2 class={cardTitleTheme()}>
      {#if allowMultiple}Drop your images here{:else}Drop your image here{/if}
    </h2>
    <p
      class="text-slate-500 dark:text-slate-400 text-base sm:text-lg font-light max-w-xs sm:max-w-md mx-auto"
    >
      or click to browse from your device
    </p>
  </div>
</div>
