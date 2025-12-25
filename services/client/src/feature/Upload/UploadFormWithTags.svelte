<script lang="ts">
  import { UploadForm } from '@slink/feature/Upload';
  import {
    TagsOption,
    UploadOptionsPanel,
  } from '@slink/feature/Upload/UploadOptions';

  import { page } from '$app/state';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { settings } from '@slink/lib/settings';

  interface Props {
    disabled?: boolean;
    processing?: boolean;
    allowMultiple?: boolean;
    selectedTags?: Tag[];
    onchange?: (files: File[]) => void;
    onTagsChange?: (tags: Tag[]) => void;
  }

  let {
    disabled = false,
    processing = false,
    allowMultiple = false,
    selectedTags = [],
    onchange,
    onTagsChange,
  }: Props = $props();

  const isUserAuthenticated = $derived(page.data.user);
  const showOptions = $derived(!disabled && isUserAuthenticated);

  const uploadOptionsSettings = settings.get(
    'uploadOptions',
    page.data.settings.uploadOptions || { expanded: false },
  );
  const { expanded } = uploadOptionsSettings;

  let optionsPanelOpen = $state($expanded ?? false);

  $effect(() => {
    if ($expanded !== optionsPanelOpen) {
      settings.set('uploadOptions', { expanded: optionsPanelOpen });
    }
  });
</script>

<div class="space-y-3">
  <UploadForm {disabled} {processing} {allowMultiple} {onchange} />

  {#if showOptions}
    <UploadOptionsPanel bind:open={optionsPanelOpen} disabled={processing}>
      <TagsOption {selectedTags} {onTagsChange} disabled={processing} />
    </UploadOptionsPanel>
  {/if}
</div>
