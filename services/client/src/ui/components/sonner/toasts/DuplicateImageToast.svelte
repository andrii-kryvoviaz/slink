<script lang="ts">
  import { cva } from 'class-variance-authority';

  import { formatDate } from '$lib/utils/date.svelte';
  import { navigateToUrl } from '$lib/utils/navigation/navigate.js';
  import Icon from '@iconify/svelte';

  import BaseToast from './BaseToast.svelte';

  const viewButton = cva([
    'group inline-flex w-fit items-center gap-1.5 self-start rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors duration-200',
    'text-accent-text bg-accent/15 hover:bg-accent/25',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/30',
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

<BaseToast variant="accent" icon="heroicons:document-duplicate" {oncloseToast}>
  <div class="flex flex-col gap-3">
    <div class="flex items-center gap-2">
      <span class="text-sm font-medium text-accent-text">
        Image Already Exists
      </span>
      <span
        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-wash text-accent-text dark:bg-accent-wash/60"
      >
        Duplicate
      </span>
    </div>

    {#if duplicateImageData}
      <div
        class="flex flex-wrap items-center gap-x-2 gap-y-1.5 text-sm text-accent-text"
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
      <p class="text-sm text-accent-text">
        {@html message}
      </p>
    {/if}
  </div>
</BaseToast>
