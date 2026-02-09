<script lang="ts">
  import { UploadForm } from '@slink/feature/Upload';
  import {
    CollectionsOption,
    TagsOption,
  } from '@slink/feature/Upload/UploadOptions';

  import { page } from '$app/state';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    disabled?: boolean;
    processing?: boolean;
    allowMultiple?: boolean;
    selectedTags?: Tag[];
    selectedCollections?: CollectionResponse[];
    onchange?: (files: File[]) => void;
    onTagsChange?: (tags: Tag[]) => void;
    onCollectionsChange?: (collections: CollectionResponse[]) => void;
  }

  let {
    disabled = false,
    processing = false,
    allowMultiple = false,
    selectedTags = [],
    selectedCollections = [],
    onchange,
    onTagsChange,
    onCollectionsChange,
  }: Props = $props();

  const isUserAuthenticated = $derived(page.data.user);
  const showOptions = $derived(!disabled && isUserAuthenticated);
</script>

<div class="space-y-4">
  <UploadForm {disabled} {processing} {allowMultiple} {onchange} />

  {#if showOptions}
    <div class="flex flex-wrap items-center gap-2">
      <TagsOption {selectedTags} {onTagsChange} disabled={processing} />
      <CollectionsOption
        {selectedCollections}
        {onCollectionsChange}
        disabled={processing}
      />
    </div>
  {/if}
</div>
