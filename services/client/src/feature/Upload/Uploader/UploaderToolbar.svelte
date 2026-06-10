<script lang="ts">
  import { page } from '$app/state';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionResponse } from '@slink/api/Response';

  import * as UploadOptions from '../Options';
  import type { Visibility } from '../Options/VisibilityPreferenceState.svelte';

  interface Props {
    disabled?: boolean;
    processing?: boolean;
    selectedTags?: Tag[];
    selectedCollections?: CollectionResponse[];
    visibility?: Visibility;
    allowOnlyPublicImages?: boolean;
    onTagsChange?: (tags: Tag[]) => void;
    onCollectionsChange?: (collections: CollectionResponse[]) => void;
  }

  let {
    disabled = false,
    processing = false,
    selectedTags = [],
    selectedCollections = [],
    visibility = 'private',
    allowOnlyPublicImages = false,
    onTagsChange,
    onCollectionsChange,
  }: Props = $props();

  const isUserAuthenticated = $derived(page.data.user);
  const showOptions = $derived(!disabled && isUserAuthenticated);
</script>

{#if showOptions}
  <div class="flex flex-wrap items-center gap-2">
    {#if !allowOnlyPublicImages}
      <UploadOptions.Visibility {visibility} disabled={processing} />
    {/if}
    <UploadOptions.Tags {selectedTags} {onTagsChange} disabled={processing} />
    <UploadOptions.Collections
      {selectedCollections}
      {onCollectionsChange}
      disabled={processing}
    />
  </div>
{/if}
