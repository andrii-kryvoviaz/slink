<script lang="ts">
  import { UploadForm } from '@slink/feature/Upload';
  import {
    AutoGroupOption,
    CollectionsOption,
    TagsOption,
    VisibilityOption,
  } from '@slink/feature/Upload/UploadOptions';
  import type { Visibility } from '@slink/feature/Upload/UploadOptions/VisibilityPreferenceState.svelte';

  import { page } from '$app/state';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    disabled?: boolean;
    processing?: boolean;
    allowMultiple?: boolean;
    selectedTags?: Tag[];
    selectedCollections?: CollectionResponse[];
    visibility?: Visibility;
    allowOnlyPublicImages?: boolean;
    autoGroupBatchUploads?: boolean;
    autoGroupPending?: boolean;
    onchange?: (files: File[]) => void;
    onTagsChange?: (tags: Tag[]) => void;
    onCollectionsChange?: (collections: CollectionResponse[]) => void;
    onAutoGroupChange?: (value: boolean) => void;
  }

  let {
    disabled = false,
    processing = false,
    allowMultiple = false,
    selectedTags = [],
    selectedCollections = [],
    visibility = 'private',
    allowOnlyPublicImages = false,
    autoGroupBatchUploads = true,
    autoGroupPending = false,
    onchange,
    onTagsChange,
    onCollectionsChange,
    onAutoGroupChange,
  }: Props = $props();

  const isUserAuthenticated = $derived(page.data.user);
  const showOptions = $derived(!disabled && isUserAuthenticated);
  const showAutoGroup = $derived(
    showOptions && selectedCollections.length === 0,
  );
</script>

<div class="space-y-4">
  <UploadForm {disabled} {processing} {allowMultiple} {onchange} />

  {#if showOptions}
    <div class="flex flex-wrap items-center gap-2">
      {#if !allowOnlyPublicImages}
        <VisibilityOption {visibility} disabled={processing} />
      {/if}
      {#if showAutoGroup && onAutoGroupChange}
        <AutoGroupOption
          enabled={autoGroupBatchUploads}
          disabled={processing}
          pending={autoGroupPending}
          onToggle={onAutoGroupChange}
        />
      {/if}
      <TagsOption {selectedTags} {onTagsChange} disabled={processing} />
      <CollectionsOption
        {selectedCollections}
        {onCollectionsChange}
        disabled={processing}
      />
    </div>
  {/if}
</div>
