<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { ImageTagList, TagSelector } from '@slink/feature/Tag';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  interface Props {
    imageId: string;
    variant?: 'default' | 'neon' | 'minimal';
    disabled?: boolean;
    initialTags?: Tag[];
  }

  let {
    imageId,
    variant = 'default',
    disabled = false,
    initialTags = [],
  }: Props = $props();

  let assignedTags = $state<Tag[]>(initialTags);
  let isExpanded = $state(false);

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
    const currentTagIds = new Set(assignedTags.map((tag) => tag.id));
    const newTagIds = new Set(newTags.map((tag) => tag.id));

    for (const tag of newTags) {
      if (!currentTagIds.has(tag.id)) {
        await assignTag(imageId, tag.id);

        if ($assignTagError) {
          printErrorsAsToastMessage($assignTagError);
          return;
        }

        assignedTags = [...assignedTags, tag];
      }
    }

    for (const tag of assignedTags) {
      if (!newTagIds.has(tag.id)) {
        await removeTag(imageId, tag.id);

        if ($removeTagError) {
          printErrorsAsToastMessage($removeTagError);
          return;
        }

        assignedTags = assignedTags.filter((t) => t.id !== tag.id);
      }
    }
  };

  const toggleExpanded = () => {
    if (!disabled) {
      isExpanded = !isExpanded;
    }
  };

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
      assignedTags = $loadedImageTags || [];
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
          icon={isExpanded ? 'lucide:edit-2' : 'lucide:plus'}
          class="h-4 w-4"
        />
        {isExpanded ? 'Done' : 'Edit'}
      </button>
    {/if}
  </div>

  <div class="space-y-3">
    {#if !isExpanded}
      <ImageTagList
        {imageId}
        {variant}
        removable={false}
        showImageCount={false}
        initialTags={assignedTags}
        onTagRemove={(tagId) => {
          assignedTags = assignedTags.filter((tag) => tag.id !== tagId);
        }}
      />
    {/if}

    {#if isExpanded && !disabled}
      <TagSelector
        selectedTags={assignedTags}
        onTagsChange={handleTagsChange}
        {variant}
        placeholder="Search or add tags..."
        disabled={isLoading}
      />
    {/if}
  </div>
</div>
