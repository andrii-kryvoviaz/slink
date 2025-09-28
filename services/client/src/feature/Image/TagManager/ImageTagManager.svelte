<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ImageTagList, TagSelector } from '@slink/feature/Tag';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useImageTagManagerState } from '@slink/lib/state/ImageTagManagerState.svelte';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  interface Props {
    imageId: string;
    variant?: 'default' | 'neon' | 'minimal';
    disabled?: boolean;
    initialTags?: Tag[];
    on?: {
      tagsUpdate?: (tags: Tag[]) => void;
    };
  }

  let {
    imageId,
    variant = 'default',
    disabled = false,
    initialTags = [],
    on,
  }: Props = $props();

  const tagManagerState = useImageTagManagerState();

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
    isLoading: isAssigningTag,
    error: assignTagError,
    run: assignTag,
  } = ReactiveState(async (imageId: string, tagId: string) => {
    await ApiClient.tag.tagImage(imageId, tagId);
  });

  const {
    isLoading: isRemovingTag,
    error: removeTagError,
    run: removeTag,
  } = ReactiveState(async (imageId: string, tagId: string) => {
    await ApiClient.tag.untagImage(imageId, tagId);
  });

  const handleTagsChange = async (newTags: Tag[]) => {
    const update = tagManagerState.updateTagsOptimistic(newTags);

    if (!update.hasChanges) {
      return;
    }

    try {
      const addPromises = update.tagsToAdd.map((tag: Tag) =>
        assignTag(imageId, tag.id),
      );
      const removePromises = update.tagsToRemove.map((tag: Tag) =>
        removeTag(imageId, tag.id),
      );

      await Promise.all([...addPromises, ...removePromises]);

      if ($assignTagError || $removeTagError) {
        update.rollback();

        if ($assignTagError) {
          printErrorsAsToastMessage($assignTagError);
        }
        if ($removeTagError) {
          printErrorsAsToastMessage($removeTagError);
        }
      } else {
        on?.tagsUpdate?.(newTags);
      }
    } catch {
      update.rollback();

      if ($assignTagError) {
        printErrorsAsToastMessage($assignTagError);
      }
      if ($removeTagError) {
        printErrorsAsToastMessage($removeTagError);
      }
    }
  };

  const toggleExpanded = () => {
    if (!disabled) {
      tagManagerState.toggleExpanded();
    }
  };

  $effect(() => {
    if (tagManagerState.imageId === imageId) {
      return;
    }

    tagManagerState.initialize(imageId, initialTags);
  });

  $effect(() => {
    if (imageId && initialTags.length === 0) {
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
      tagManagerState.setAssignedTags($loadedImageTags || []);
    }
  });

  const isLoading = $derived(
    $isLoadingTags || $isAssigningTag || $isRemovingTag,
  );
</script>

<div class="space-y-4">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tags</h3>
      {#if isLoading}
        <Loader variant="minimal" size="xs" />
      {/if}
    </div>

    {#if !disabled}
      <button
        onclick={toggleExpanded}
        class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-lg transition-colors"
        disabled={isLoading}
      >
        <Icon
          icon={tagManagerState.isExpanded ? 'lucide:edit-2' : 'lucide:plus'}
          class="h-4 w-4"
        />
        {tagManagerState.isExpanded ? 'Done' : 'Edit'}
      </button>
    {/if}
  </div>

  <div class="space-y-3">
    {#if !tagManagerState.isExpanded}
      <ImageTagList
        {imageId}
        {variant}
        removable={false}
        showImageCount={false}
        initialTags={tagManagerState.assignedTags}
        onTagRemove={(tagId) => {
          tagManagerState.removeTag(tagId);
        }}
      />
    {/if}

    {#if tagManagerState.isExpanded && !disabled}
      <TagSelector
        selectedTags={tagManagerState.assignedTags}
        onTagsChange={handleTagsChange}
        {variant}
        placeholder="Search or add tags..."
        disabled={isLoading}
      />
    {/if}
  </div>
</div>
