<script lang="ts">
  import { formatDate } from '$lib/utils/date.js';
  import { navigateToUrl } from '$lib/utils/navigation/navigate.js';

  import BaseToast from './BaseToast.svelte';

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
      <div class="space-y-3">
        <p class="text-sm text-purple-700 dark:text-purple-300/90">
          This <button
            type="button"
            class="font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200 underline underline-offset-2 transition-colors duration-200"
            onclick={viewExistingImage}
          >
            image
          </button>
          was already uploaded
          <span class="font-medium"
            >{formatDate(duplicateImageData.uploadedAt).toLowerCase()}</span
          >.
        </p>
      </div>
    {:else}
      <p class="text-sm text-purple-700 dark:text-purple-300/90">
        {@html message}
      </p>
    {/if}
  </div>
</BaseToast>
