<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { UploadForm } from '@slink/feature/Upload';

  import { page } from '$app/state';

  import type { Tag } from '@slink/api/Resources/TagResource';

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
</script>

<div class="space-y-6">
  {#if !disabled && isUserAuthenticated}
    <TagSelector
      {selectedTags}
      {onTagsChange}
      {disabled}
      placeholder="Add tags..."
      variant="neon"
    />
  {/if}

  <UploadForm {disabled} {processing} {allowMultiple} {onchange} />
</div>
