<script lang="ts">
  import {
    TagBadge,
    TagDropdownContent,
    TagInput,
    tagDropdownContentVariants,
    tagSelectorContainerVariants,
    tagSelectorIconContainerVariants,
    tagSelectorIconVariants,
    useTagOperations,
  } from '@slink/feature/Tag';
  import type { TagSelectorContainerVariants } from '@slink/feature/Tag';
  import { Popover } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui/index.js';

  interface Props extends TagSelectorContainerVariants {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
    placeholder?: string;
    variant?: 'default' | 'neon' | 'minimal' | 'subtle';
    allowCreate?: boolean;
    hideIcon?: boolean;
    singleSelect?: boolean;
  }

  let {
    selectedTags = [],
    onTagsChange,
    disabled = false,
    placeholder = 'Search or add tags...',
    variant = 'default',
    allowCreate = true,
    hideIcon = false,
    singleSelect = false,
  }: Props = $props();

  let isOpen = $state(false);
  let searchTerm = $state('');
  let creatingChildFor = $state<Tag | null>(null);
  let childTagName = $state('');
  let inputRef = $state<HTMLInputElement>();
  let childInputRef = $state<HTMLInputElement>();
  let highlightedIndex = $state(-1);

  export const focusInput = () => {
    inputRef?.focus();
  };

  const hasSelectedTags = $derived(selectedTags.length > 0);
  const selectedTagIds = $derived(new Set(selectedTags.map((tag) => tag.id)));

  const {
    loadTags,
    isLoadingTags,
    tagsResponse,
    createTag,
    isCreatingTag,
    createdTagId,
    resetCreateTag,
    fetchCreatedTag,
    createdTag,
    resetCreatedTag,
  } = useTagOperations();

  const availableTags = $derived($tagsResponse?.data || []);
  const shouldShowLoader = $derived(
    $isLoadingTags && isOpen && !availableTags.length,
  );

  const filteredTags = $derived(
    availableTags.filter(
      (tag: Tag) =>
        !selectedTagIds.has(tag.id) &&
        tag.name.toLowerCase().includes(searchTerm.toLowerCase()),
    ),
  );

  const canCreate = $derived(
    Boolean(
      allowCreate &&
      searchTerm.trim() &&
      !availableTags.find(
        (t: Tag) => t.name.toLowerCase() === searchTerm.toLowerCase(),
      ),
    ),
  );

  const visibleItemsCount = $derived(() => {
    let count = 0;
    if (canCreate && !creatingChildFor) count++;
    if (!creatingChildFor) count += filteredTags.length;
    return count;
  });

  const resetDropdownState = () => {
    searchTerm = '';
    isOpen = false;
    creatingChildFor = null;
    childTagName = '';
    highlightedIndex = -1;
  };

  const closePopover = (blur = false) => {
    isOpen = false;
    highlightedIndex = -1;

    if (blur && !creatingChildFor) {
      childInputRef?.blur();
      inputRef?.blur();
    }
  };

  const selectTag = (tag: Tag, keepOpen = false) => {
    if (disabled || selectedTagIds.has(tag.id)) return;

    const newTags = singleSelect ? [tag] : [...selectedTags, tag];
    onTagsChange?.(newTags);

    if (!keepOpen || singleSelect) {
      resetDropdownState();
    } else {
      searchTerm = '';
      highlightedIndex = -1;
    }
  };

  const removeTag = (tagId: string) => {
    if (disabled) return;
    const newTags = selectedTags.filter((t) => t.id !== tagId);
    onTagsChange?.(newTags);
  };

  const startCreatingChild = (parentTag: Tag) => {
    if (disabled) return;
    creatingChildFor = parentTag;
    childTagName = '';
    searchTerm = '';

    setTimeout(() => {
      childInputRef?.focus();
    }, 0);
  };

  const cancelChildCreation = () => {
    creatingChildFor = null;
    childTagName = '';
  };

  const handleCreateTag = () => {
    if (disabled) return;
    if (creatingChildFor && childTagName.trim()) {
      createTag(childTagName.trim(), creatingChildFor.id, availableTags);
    } else if (searchTerm.trim()) {
      createTag(searchTerm.trim(), undefined, availableTags);
    }
  };

  const isCreateItemHighlighted = () => {
    return canCreate && !creatingChildFor && highlightedIndex === 0;
  };

  const getHighlightedTag = () => {
    const tagIndex =
      canCreate && !creatingChildFor ? highlightedIndex - 1 : highlightedIndex;
    return filteredTags[tagIndex];
  };

  const handleHighlightedSelection = () => {
    if (isCreateItemHighlighted()) {
      handleCreateTag();
    } else {
      const tag = getHighlightedTag();
      if (tag) selectTag(tag);
    }
  };

  const handleDefaultSelection = () => {
    if (canCreate) {
      handleCreateTag();
    } else if (filteredTags[0]) {
      selectTag(filteredTags[0]);
    }
  };

  const handleEnter = () => {
    if (highlightedIndex >= 0) {
      handleHighlightedSelection();
    } else {
      handleDefaultSelection();
    }
  };

  const handleArrowDown = () => {
    if (!isOpen) {
      isOpen = true;
      loadTags(searchTerm);
      return;
    }
    const maxIndex = visibleItemsCount() - 1;
    if (maxIndex < 0) return;
    highlightedIndex = highlightedIndex < maxIndex ? highlightedIndex + 1 : 0;
  };

  const handleArrowUp = () => {
    if (!isOpen) return;
    const maxIndex = visibleItemsCount() - 1;
    if (maxIndex < 0) return;
    highlightedIndex = highlightedIndex > 0 ? highlightedIndex - 1 : maxIndex;
  };

  const handleBackspace = () => {
    if (selectedTags.length > 0) {
      removeTag(selectedTags[selectedTags.length - 1].id);
    }
  };

  const handleContainerClick = () => {
    if (!disabled) {
      inputRef?.focus();
      isOpen = true;
    }
  };

  const handleContainerKeydown = (e: KeyboardEvent) => {
    if (disabled) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      inputRef?.focus();
    }
  };

  $effect(() => {
    if (isOpen) {
      loadTags(searchTerm);
    }
  });

  $effect(() => {
    searchTerm;
    highlightedIndex = -1;
  });

  $effect(() => {
    if (disabled) {
      isOpen = false;
      highlightedIndex = -1;
      cancelChildCreation();
    }
  });

  $effect(() => {
    if ($createdTagId?.id) {
      fetchCreatedTag($createdTagId.id);
      resetCreateTag();
    }
  });

  $effect(() => {
    if ($createdTag && !selectedTagIds.has($createdTag.id)) {
      selectTag($createdTag, true);

      cancelChildCreation();
      resetCreatedTag();
    }
  });
</script>

<div class="space-y-3">
  <div class="relative">
    <Popover.Root bind:open={isOpen}>
      <Popover.Trigger disabled={true}>
        {#snippet child({ props })}
          <div
            {...props}
            class={cn(
              tagSelectorContainerVariants({
                variant,
                disabled,
              }),
            )}
            role="combobox"
            aria-expanded={!disabled && isOpen}
            aria-controls="tag-dropdown"
            aria-disabled={disabled}
            tabindex={disabled ? -1 : 0}
            onkeydown={handleContainerKeydown}
            onclick={handleContainerClick}
          >
            <div class="flex items-center gap-3 relative z-10">
              {#if !hideIcon}
                <div class="shrink-0">
                  <div class={tagSelectorIconContainerVariants({ variant })}>
                    {#if shouldShowLoader}
                      <Icon
                        icon="ph:spinner"
                        class={`${tagSelectorIconVariants({ variant })} animate-spin`}
                      />
                    {:else}
                      <Icon
                        icon="ph:tag"
                        class={tagSelectorIconVariants({ variant })}
                      />
                    {/if}
                  </div>
                </div>
              {/if}

              <div class="flex flex-wrap items-center gap-1.5 flex-1">
                {#each selectedTags as tag (tag.id)}
                  <TagBadge
                    {tag}
                    variant="neon"
                    showFullPath={true}
                    showCount={false}
                    onClose={() => removeTag(tag.id)}
                  />
                {/each}

                <div class="flex-1 min-w-30">
                  <TagInput
                    bind:ref={inputRef}
                    bind:childRef={childInputRef}
                    bind:searchTerm
                    bind:childTagName
                    {variant}
                    placeholder={hasSelectedTags && !singleSelect
                      ? 'Add more tags...'
                      : placeholder}
                    {creatingChildFor}
                    onSearchChange={(value) => {
                      searchTerm = value;
                      if (disabled) return;
                      isOpen = true;
                    }}
                    onEnter={handleEnter}
                    onEscape={() => {
                      closePopover(true);
                    }}
                    onBackspace={handleBackspace}
                    onArrowDown={handleArrowDown}
                    onArrowUp={handleArrowUp}
                    onfocus={() => {}}
                    onCancelChild={cancelChildCreation}
                    onCreateChild={handleCreateTag}
                    {disabled}
                  />
                </div>
              </div>
            </div>
          </div>
        {/snippet}
      </Popover.Trigger>

      <Popover.Content
        id="tag-dropdown"
        class={tagDropdownContentVariants({ variant })}
        sideOffset={4}
        avoidCollisions={true}
        collisionPadding={10}
        trapFocus={false}
        interactOutsideBehavior="close"
        onOpenAutoFocus={(e) => e.preventDefault()}
        onInteractOutside={() => closePopover(true)}
        onFocusOutside={() => closePopover(true)}
        onEscapeKeydown={() => {
          closePopover(true);
        }}
      >
        <TagDropdownContent
          isOpen={!disabled && isOpen}
          tags={filteredTags}
          {searchTerm}
          {creatingChildFor}
          {childTagName}
          isCreating={$isCreatingTag}
          isLoading={$isLoadingTags}
          {canCreate}
          {allowCreate}
          {variant}
          {highlightedIndex}
          onSelectTag={selectTag}
          onAddChild={startCreatingChild}
          onCreateTag={handleCreateTag}
        />
      </Popover.Content>
    </Popover.Root>
  </div>
</div>
