<script lang="ts">
  import { TagBadge } from '@slink/feature/Tag';

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

  const {
    isLoading: isLoadingTags,
    error: loadTagsError,
    data: loadedTags,
    run: loadTags,
  } = ReactiveState<Tag[]>(async (id: string) => {
    const response = await ApiClient.tag.getImageTags(id);
    return response.data;
  });

  const tags = $derived(
    initialTags.length > 0 ? initialTags : $loadedTags || [],
  );

  const handleRemoveTag = (tagId: string) => {
    if (disabled || !removable) return;
    onTagRemove?.(tagId);
  };

  let hasLoaded = $state(false);

  $effect(() => {
    if (browser && imageId && initialTags.length === 0 && !hasLoaded) {
      hasLoaded = true;
      loadTags(imageId);
    }
  });

  $effect(() => {
    if ($loadTagsError) {
      printErrorsAsToastMessage($loadTagsError);
    }
  });
</script>

{#if tags.length > 0}
  <div class="flex flex-wrap gap-2">
    {#each tags as tag (tag.id)}
      <TagBadge
        {tag}
        {variant}
        showFullPath={false}
        showCount={showImageCount}
        onClose={removable ? () => handleRemoveTag(tag.id) : undefined}
      />
    {/each}
  </div>
{:else if !$isLoadingTags}
  <p class="text-sm text-gray-500 dark:text-gray-400">
    No tags assigned to this image.
  </p>
{/if}
