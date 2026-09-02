<script lang="ts">
  import BaseToast from './BaseToast.svelte';

  interface Props {
    message?: string;
    oncloseToast?: () => void;
  }

  let { message = '', oncloseToast }: Props = $props();
</script>

<BaseToast variant="accent" icon="clarity:block-line" {oncloseToast}>
  <div class="flex flex-col gap-2">
    <div class="text-sm font-medium text-accent-text">
      Unsupported file format
    </div>
    <p class="text-sm text-accent-text">
      {#if message}
        {@html message}
      {:else}
        The file you're trying to upload is not supported. Please use one of the
        supported image formats.
      {/if}
    </p>
  </div>
</BaseToast>
