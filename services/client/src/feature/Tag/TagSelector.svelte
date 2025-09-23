<script lang="ts">
  import { cva } from 'class-variance-authority';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import TagDropdownContent from './TagDropdownContent.svelte';
  import TagPill from './TagPill.svelte';
  import TagSearchInput from './TagSearchInput.svelte';
  import { useTagOperations } from './useTagOperations.svelte';

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
    placeholder?: string;
    variant?: 'default' | 'neon' | 'minimal';
  }

  let {
    selectedTags = [],
    onTagsChange,
    disabled = false,
    placeholder = 'Search or add tags...',
    variant = 'default',
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
    if (isOpen && searchTerm?.trim()) {
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

  const containerVariants = cva(
    'min-h-[3.5rem] w-full px-4 py-3 transition-all duration-300 rounded-xl',
    {
      variants: {
        variant: {
          default: [
            'bg-background border border-input shadow-sm',
            'focus-within:border-ring focus-within:ring-1 focus-within:ring-ring/30',
          ],
          neon: [
            'relative overflow-hidden',
            'bg-gradient-to-br from-white via-white to-slate-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900',
            'border border-indigo-200/60 dark:border-indigo-700/60',
            'shadow-xl shadow-indigo-500/5 dark:shadow-indigo-500/10',
            'backdrop-blur-sm',
            'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_1px_1px,rgba(99,102,241,0.15)_1px,transparent_0)] before:bg-[length:20px_20px] dark:before:bg-[radial-gradient(circle_at_1px_1px,rgba(129,140,248,0.2)_1px,transparent_0)]',
            'focus-within:border-indigo-400/60 dark:focus-within:border-indigo-500/60',
            'focus-within:shadow-xl focus-within:shadow-indigo-500/15 dark:focus-within:shadow-indigo-500/20',
            'hover:border-indigo-300/50 dark:hover:border-indigo-600/50',
            'hover:shadow-lg hover:shadow-indigo-500/10 dark:hover:shadow-indigo-500/15',
          ],
          minimal: [
            'bg-transparent border border-slate-200/50 dark:border-slate-700/50',
            'focus-within:border-slate-300 dark:focus-within:border-slate-600',
          ],
        },
        disabled: {
          true: 'opacity-50 cursor-not-allowed',
          false: '',
        },
      },
      defaultVariants: {
        variant: 'default',
        disabled: false,
      },
    },
  );
</script>

<div class="space-y-3">
  {#if !disabled}
    <div class="relative">
      <div
        class={containerVariants({ variant, disabled })}
        role="combobox"
        aria-expanded={isOpen}
        aria-controls="tag-dropdown"
        tabindex="0"
        onkeydown={handleContainerKeydown}
        onclick={handleContainerClick}
      >
        <div class="flex items-center gap-3 relative z-10">
          {#if variant === 'neon'}
            <div class="flex-shrink-0">
              <div
                class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center border border-indigo-200/50 dark:border-purple-300/30 transition-all duration-300 backdrop-blur-sm ring-1 ring-indigo-100/20 dark:ring-purple-300/20 [filter:drop-shadow(0_0_4px_rgba(99,102,241,0.2))_drop-shadow(0_0_8px_rgba(99,102,241,0.1))] [box-shadow:0_0_0_1px_rgba(99,102,241,0.1),0_0_20px_rgba(99,102,241,0.15)]"
              >
                <Icon
                  icon="ph:tag"
                  class="h-4 w-4 text-indigo-600 dark:text-purple-400 transition-all duration-300"
                />
              </div>
            </div>
          {/if}

          <div class="flex flex-wrap items-center gap-1.5 flex-1">
            {#each selectedTags as tag (tag.id)}
              <TagPill
                {tag}
                variant="default"
                onRemove={() => removeTag(tag.id)}
              />
            {/each}

            <div class="flex-1 min-w-[120px]">
              <TagSearchInput
                bind:ref={inputRef}
                bind:childRef={childInputRef}
                bind:searchTerm
                bind:childTagName
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
                onFocus={() => {
                  isOpen = true;
                }}
                onBlur={() => {
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
        isLoading={shouldShowLoader}
        tags={filteredTags}
        {searchTerm}
        {creatingChildFor}
        {childTagName}
        isCreating={$isCreatingTag}
        {canCreate}
        onSelectTag={selectTag}
        onAddChild={startCreatingChild}
        onCreateTag={handleCreateTag}
      />
    </div>
  {/if}
</div>
