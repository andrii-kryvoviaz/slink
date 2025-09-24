<script lang="ts">
  import { TagPill } from '@slink/feature/Tag';

  import { browser } from '$app/environment';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  interface Props {
    imageId: string;
    variant?: 'default' | 'neon' | 'minimal';
    showImageCount?: boolean;
    removable?: boolean;
    initialTags?: Tag[];
    onTagRemove?: (tagId: string) => void;
    disabled?: boolean;
  }

  let {
    imageId,
    variant = 'default',
    showImageCount = false,
    removable = false,
    initialTags = [],
    onTagRemove,
    disabled = false,
  }: Props = $props();

  let tags = $state<Tag[]>(initialTags);

  const {
    isLoading: isLoadingTags,
    error: loadTagsError,
    data: loadedImageTags,
    run: loadImageTags,
  } = ReactiveState<Tag[]>(async (id: string) => {
    const response = await ApiClient.tag.getImageTags(id);
    return response.data;
  });

  const {
    isLoading: isRemovingTag,
    error: removeTagError,
    run: removeTag,
  } = ReactiveState(async (imageId: string, tagId: string) => {
    await ApiClient.tag.untagImage(imageId, tagId);
  });

  const handleRemoveTag = async (tagId: string) => {
    if (disabled || !removable) return;

    await removeTag(imageId, tagId);

    if ($removeTagError) {
      printErrorsAsToastMessage($removeTagError);
      return;
    }

    tags = tags.filter((tag) => tag.id !== tagId);
    onTagRemove?.(tagId);
  };

  $effect(() => {
    if (browser && imageId && initialTags.length === 0) {
      loadImageTags(imageId);
    }
  });

  $effect(() => {
    if ($loadTagsError) {
      printErrorsAsToastMessage($loadTagsError);
    }
  });

  $effect(() => {
    if (!$isLoadingTags && $loadedImageTags) {
      tags = $loadedImageTags || [];
    }
  });

  const isLoading = $derived($isLoadingTags || $isRemovingTag);
</script>

{#if tags.length > 0}
  <div class="flex flex-wrap gap-2">
    {#each tags as tag (tag.id)}
      <TagPill
        {tag}
        {variant}
        {removable}
        {showImageCount}
        onRemove={() => handleRemoveTag(tag.id)}
      />
    {/each}
  </div>
{:else if !isLoading}
  <p class="text-sm text-gray-500 dark:text-gray-400">
    No tags assigned to this image.
  </p>
{/if}
