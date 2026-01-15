<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { TagBadge, TagSelector } from '@slink/feature/Tag';

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
  let containerRef = $state<HTMLDivElement>();
  let tagSelectorRef = $state<{ focusInput: () => void }>();

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
    } finally {
      setTimeout(() => tagSelectorRef?.focusInput(), 0);
    }
  };

  const startEditing = (event?: MouseEvent) => {
    event?.stopPropagation();
    if (!disabled) {
      tagManagerState.setExpanded(true);
      setTimeout(() => tagSelectorRef?.focusInput(), 0);
    }
  };

  const stopEditing = () => {
    tagManagerState.setExpanded(false);
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (
      tagManagerState.isExpanded &&
      containerRef &&
      !containerRef.contains(event.target as Node)
    ) {
      stopEditing();
    }
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && tagManagerState.isExpanded) {
      stopEditing();
    }
  };

  $effect(() => {
    if (tagManagerState.imageId !== imageId) {
      tagManagerState.initialize(imageId, initialTags);

      if (initialTags.length === 0) {
        loadImageTags(imageId);
      }
    }
  });

  if ($loadTagsError) {
    printErrorsAsToastMessage($loadTagsError);
  }

  const isLoading = $derived(
    $isLoadingTags || $isAssigningTag || $isRemovingTag,
  );
  const hasTags = $derived(tagManagerState.assignedTags.length > 0);
</script>

<svelte:window onclick={handleClickOutside} onkeydown={handleKeyDown} />

<div class="space-y-1.5" bind:this={containerRef}>
  <div class="flex items-center gap-2">
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
      Tags
    </span>
    {#if isLoading}
      <Loader variant="minimal" size="xs" />
    {/if}
  </div>

  {#if tagManagerState.isExpanded && !disabled}
    <TagSelector
      bind:this={tagSelectorRef}
      selectedTags={tagManagerState.assignedTags}
      onTagsChange={handleTagsChange}
      {variant}
      placeholder="Add more tags..."
      disabled={isLoading}
      hideIcon={true}
    />
  {:else}
    <button
      onclick={startEditing}
      {disabled}
      class="w-full text-left rounded-lg py-2.5 px-3 -mx-3 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50 group cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
    >
      {#if hasTags}
        <div class="flex flex-wrap gap-2">
          {#each tagManagerState.assignedTags as tag (tag.id)}
            <TagBadge {tag} {variant} showFullPath={false} showCount={false} />
          {/each}
          {#if !disabled}
            <span
              class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
            >
              <Icon icon="ph:plus" class="w-3.5 h-3.5" />
              Add
            </span>
          {/if}
        </div>
      {:else}
        <p
          class="text-sm text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors flex items-center gap-1.5"
        >
          <Icon icon="ph:tag" class="w-4 h-4" />
          Click to add tags...
        </p>
      {/if}
    </button>
  {/if}
</div>
