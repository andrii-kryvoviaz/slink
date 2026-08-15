<script lang="ts">
  import { cardTitleTheme } from '@slink/ui/components/card';
  import { useDropzone } from '@slink/ui/components/dropzone';

  import Icon from '@iconify/svelte';

  const dropzone = useDropzone();

  const allowMultiple = $derived(dropzone.multiple);
</script>

<div class="relative z-10 flex flex-col items-center">
  <div class="mb-5">
    <div
      class="relative w-16 h-16 rounded-2xl bg-linear-to-br from-accent/20 to-accent/5 dark:from-decor-violet-strong/35 dark:to-decor-violet-strong/10 flex items-center justify-center transition-transform duration-150 ease-out"
      class:scale-105={dropzone.isDragOver}
    >
      <Icon
        icon="lucide:upload"
        class="h-[26px] w-[26px] text-accent dark:text-accent-strong"
      />
      <div
        class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-success-solid flex items-center justify-center ring-[3px] ring-card"
      >
        <Icon icon="ph:plus-bold" class="h-3 w-3 text-on-success" />
      </div>
    </div>
  </div>

  <div class="space-y-1.5">
    <h2 class={cardTitleTheme()}>
      {#if allowMultiple}Drop your images here{:else}Drop your image here{/if}
    </h2>
    <p
      class="text-foreground-muted text-base sm:text-lg font-light max-w-xs sm:max-w-md mx-auto"
    >
      or click to browse from your device
    </p>
  </div>
</div>
