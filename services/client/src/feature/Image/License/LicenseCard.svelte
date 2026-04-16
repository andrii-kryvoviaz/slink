<script lang="ts">
  import { Select } from '@slink/ui/components';

  import type { License } from '@slink/lib/enum/License';

  import { createLicenseCardState } from './LicenseCardState.svelte';

  interface Props {
    imageId: string;
    license?: string | null;
    licenses: License[];
    licensingEnabled: boolean;
    on?: {
      licenseSaved?: (license: string) => void;
    };
  }

  let {
    imageId,
    license = null,
    licenses,
    licensingEnabled,
    on,
  }: Props = $props();

  const state = createLicenseCardState({
    getImage: () => ({ id: imageId, license }),
    getLicenses: () => licenses,
    getLicensingEnabled: () => licensingEnabled,
    onLicenseSaved: (value) => on?.licenseSaved?.(value),
  });
</script>

{#if state.licensingEnabled && state.licenses.length > 0}
  <div>
    <div class="flex items-center gap-2 mb-2">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        License
      </h2>
      {#if state.isLoading}
        <span class="text-xs text-gray-500 dark:text-gray-400">Saving...</span>
      {/if}
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
      Choose how others can use this image
    </p>
    <Select
      class="w-full"
      items={state.licenseOptions}
      bind:value={state.selectedLicense}
      placeholder="Select a license..."
    />
  </div>
{/if}
