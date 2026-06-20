<script lang="ts">
  import { cva } from 'class-variance-authority';

  import { formatDate } from '$lib/utils/date.svelte';
  import { navigateToUrl } from '$lib/utils/navigation/navigate.js';
  import Icon from '@iconify/svelte';

  import BaseToast from './BaseToast.svelte';

  const viewButton = cva([
    'group inline-flex w-fit items-center gap-1.5 self-start rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors duration-200',
    'text-purple-700 bg-purple-100/60 hover:bg-purple-200/70 hover:text-purple-800',
    'dark:text-purple-300 dark:bg-purple-900/40 dark:hover:bg-purple-900/70 dark:hover:text-purple-200',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500/30',
  ]);

  interface DuplicateImageData {
    uploadedAt: string;
    existingImageUrl?: string;
  }

  interface Props {
    message?: string;
    data?: Record<string, unknown>;
    oncloseToast?: () => void;
  }

  let { message = '', data, oncloseToast }: Props = $props();

  let duplicateImageData = $derived(data as DuplicateImageData | undefined);

  let uploadedDate = $derived(
    duplicateImageData
      ? formatDate(duplicateImageData.uploadedAt).toLowerCase()
      : '',
  );

  const viewExistingImage = () => {
    if (duplicateImageData?.existingImageUrl) {
      navigateToUrl(duplicateImageData.existingImageUrl);
    }
  };
</script>

<BaseToast variant="purple" icon="heroicons:document-duplicate" {oncloseToast}>
  <div class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
      <span class="text-sm font-medium text-purple-800 dark:text-purple-200">
        Image Already Exists
      </span>
      <span
        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300"
      >
        Duplicate
      </span>
    </div>

    {#if duplicateImageData}
      <div
        class="flex flex-wrap items-center gap-x-2 gap-y-1.5 text-sm text-purple-700 dark:text-purple-300/90"
      >
        <span>This image was already uploaded {uploadedDate}.</span>
        {#if duplicateImageData.existingImageUrl}
          <button
            type="button"
            onclick={viewExistingImage}
            class={viewButton()}
          >
            <span>View image</span>
            <Icon
              icon="ph:arrow-right"
              class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
            />
          </button>
        {/if}
      </div>
    {:else}
      <p class="text-sm text-purple-700 dark:text-purple-300/90">
        {@html message}
      </p>
    {/if}
  </div>
</BaseToast>
