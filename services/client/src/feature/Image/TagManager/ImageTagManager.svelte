<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ImageTagList, TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';

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
    run: loadImageTags,
  } = ReactiveState(async (id: string) => {
    const response = await ApiClient.tag.getImageTags(id);
    if (tagManagerState.imageId === id) {
      tagManagerState.setAssignedTags(response.data);
    }
  });

  const { isLoading: isAssigningTag, run: assignTag } = ReactiveState(
    async (imageId: string, tagId: string) => {
      await ApiClient.tag.tagImage(imageId, tagId);
    },
  );

  const { isLoading: isRemovingTag, run: removeTag } = ReactiveState(
    async (imageId: string, tagId: string) => {
      await ApiClient.tag.untagImage(imageId, tagId);
    },
  );

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

      on?.tagsUpdate?.(newTags);
    } catch (error: unknown) {
      update.rollback();
      printErrorsAsToastMessage(error as Error);
    }
  };

  const toggleExpanded = () => {
    if (!disabled) {
      tagManagerState.toggleExpanded();
    }
  };

  if (tagManagerState.imageId !== imageId) {
    tagManagerState.initialize(imageId, initialTags);

    if (initialTags.length === 0) {
      loadImageTags(imageId);
    }
  }

  if ($loadTagsError) {
    printErrorsAsToastMessage($loadTagsError);
  }

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
      <Button
        onclick={toggleExpanded}
        variant="glass-violet"
        size="sm"
        disabled={isLoading}
      >
        <Icon
          icon={tagManagerState.isExpanded ? 'ph:check' : 'ph:pencil-simple'}
          class="w-4 h-4"
        />
        {tagManagerState.isExpanded ? 'Done' : 'Edit'}
      </Button>
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
