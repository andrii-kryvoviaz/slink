<script lang="ts">
  import { TagBadge } from '@slink/feature/Tag';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import TagDropdownContent from '../TagDropdown/TagDropdownContent.svelte';
  import { TagInput } from '../TagInput';
  import { useTagOperations } from '../useTagOperations.svelte';
  import {
    type TagSelectorContainerVariants,
    tagSelectorContainerVariants,
    tagSelectorIconContainerVariants,
    tagSelectorIconVariants,
  } from './TagSelector.theme';

  interface Props extends TagSelectorContainerVariants {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
    placeholder?: string;
    variant?: 'default' | 'neon' | 'minimal';
    allowCreate?: boolean;
  }

  let {
    selectedTags = [],
    onTagsChange,
    disabled = false,
    placeholder = 'Search or add tags...',
    variant = 'default',
    allowCreate = true,
  }: Props = $props();

  let isOpen = $state(false);
  let searchTerm = $state('');
  let creatingChildFor = $state<Tag | null>(null);
  let childTagName = $state('');
  let inputRef = $state<HTMLInputElement>();
  let childInputRef = $state<HTMLInputElement>();

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
  const shouldShowLoader = $derived($isLoadingTags && !!searchTerm?.trim());

  const filteredTags = $derived(
    availableTags.filter(
      (tag: Tag) =>
        !selectedTags.find((t) => t.id === tag.id) &&
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

  const resetDropdownState = () => {
    searchTerm = '';
    isOpen = false;
    creatingChildFor = null;
    childTagName = '';
  };

  const selectTag = (tag: Tag, keepOpen = false) => {
    if (selectedTags.find((t) => t.id === tag.id)) return;

    const newTags = [...selectedTags, tag];
    onTagsChange?.(newTags);

    if (!keepOpen) {
      resetDropdownState();
    } else {
      searchTerm = '';
    }
  };

  const removeTag = (tagId: string) => {
    const newTags = selectedTags.filter((t) => t.id !== tagId);
    onTagsChange?.(newTags);
  };

  const startCreatingChild = (parentTag: Tag) => {
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
    if (creatingChildFor && childTagName.trim()) {
      createTag(childTagName.trim(), creatingChildFor.id, availableTags);
    } else if (searchTerm.trim()) {
      createTag(searchTerm.trim(), undefined, availableTags);
    }
  };

  const handleEnter = () => {
    if (canCreate) {
      handleCreateTag();
    } else {
      const existingTag = filteredTags[0];
      if (existingTag) {
        selectTag(existingTag);
      }
    }
  };

  const handleBackspace = () => {
    if (selectedTags.length > 0) {
      removeTag(selectedTags[selectedTags.length - 1].id);
    }
  };

  const handleContainerClick = () => {
    if (!disabled) {
      inputRef?.focus();
    }
  };

  const handleContainerKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      inputRef?.focus();
    }
  };

  $effect(() => {
    if (searchTerm?.trim()) {
      loadTags(searchTerm);
    }
  });

  $effect(() => {
    if ($createdTagId?.id) {
      fetchCreatedTag($createdTagId.id);
      resetCreateTag();
    }
  });

  $effect(() => {
    if ($createdTag && !selectedTags.find((t) => t.id === $createdTag.id)) {
      selectTag($createdTag, true);

      cancelChildCreation();
      resetCreatedTag();
    }
  });
</script>

<div class="space-y-3">
  {#if !disabled}
    <div class="relative">
      <div
        class={tagSelectorContainerVariants({ variant, disabled })}
        role="combobox"
        aria-expanded={isOpen}
        aria-controls="tag-dropdown"
        tabindex="0"
        onkeydown={handleContainerKeydown}
        onclick={handleContainerClick}
      >
        <div class="flex items-center gap-3 relative z-10">
          <div class="flex-shrink-0">
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

          <div class="flex flex-wrap items-center gap-1.5 flex-1">
            {#each selectedTags as tag (tag.id)}
              <TagBadge
                {tag}
                {variant}
                showFullPath={true}
                showCount={false}
                onClose={() => removeTag(tag.id)}
              />
            {/each}

            <div class="flex-1 min-w-[120px]">
              <TagInput
                bind:ref={inputRef}
                bind:childRef={childInputRef}
                bind:searchTerm
                bind:childTagName
                {variant}
                placeholder={selectedTags.length === 0
                  ? placeholder
                  : 'Add more tags...'}
                {creatingChildFor}
                onSearchChange={(value) => (searchTerm = value)}
                onEnter={handleEnter}
                onEscape={() => {
                  isOpen = false;
                  inputRef?.blur();
                }}
                onBackspace={handleBackspace}
                onfocus={() => {
                  isOpen = true;
                }}
                onblur={() => {
                  setTimeout(() => {
                    isOpen = false;
                  }, 150);
                }}
                onCancelChild={cancelChildCreation}
                onCreateChild={handleCreateTag}
              />
            </div>
          </div>
        </div>
      </div>

      <TagDropdownContent
        {isOpen}
        tags={filteredTags}
        {searchTerm}
        {creatingChildFor}
        {childTagName}
        isCreating={$isCreatingTag}
        {canCreate}
        {variant}
        onSelectTag={selectTag}
        onAddChild={startCreatingChild}
        onCreateTag={handleCreateTag}
      />
    </div>
  {/if}
</div>
