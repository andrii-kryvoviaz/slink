<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { TagBadge, useTagOperations } from '@slink/feature/Tag';
  import * as Command from '@slink/ui/components/command';
  import * as Filter from '@slink/ui/components/filter';
  import type { FilterVariant } from '@slink/ui/components/filter';
  import { PickerEmptyState } from '@slink/ui/components/picker';
  import { Command as CommandPrimitive } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import {
    getTagDisplayName,
    getTagLastSegment,
    getTagParentPath,
    isTagNested,
  } from '@slink/utils/tag';

  type Variant = Exclude<FilterVariant, 'pill'>;

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
    placeholder?: string;
    variant?: Variant;
    hideIcon?: boolean;
    singleSelect?: boolean;
  }

  let {
    selectedTags = [],
    onTagsChange,
    disabled = false,
    placeholder = 'Search tags...',
    variant = 'default',
    hideIcon = false,
    singleSelect = false,
  }: Props = $props();

  let isOpen = $state(false);
  let searchTerm = $state('');
  let searchRef = $state<{ focus: () => void } | null>(null);

  export const focusInput = () => searchRef?.focus();

  const hasSelectedTags = $derived(selectedTags.length > 0);
  const selectedTagIds = $derived(new Set(selectedTags.map((t) => t.id)));

  const { loadTags, isLoadingTags, tagsResponse } = useTagOperations();

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

  const dynamicPlaceholder = $derived(
    hasSelectedTags && !singleSelect ? 'Add more tags...' : placeholder,
  );

  const selectTag = (tag: Tag) => {
    if (disabled || selectedTagIds.has(tag.id)) return;

    const newTags = singleSelect ? [tag] : [...selectedTags, tag];
    onTagsChange?.(newTags);

    if (singleSelect) {
      isOpen = false;
      searchTerm = '';
    } else {
      searchTerm = '';
    }
  };

  const removeTag = (tagId: string) => {
    if (disabled) return;
    onTagsChange?.(selectedTags.filter((t) => t.id !== tagId));
  };

  const handleEscape = () => {
    isOpen = false;
  };

  const handleBackspaceEmpty = () => {
    if (selectedTags.length > 0) {
      removeTag(selectedTags[selectedTags.length - 1].id);
    }
  };

  $effect(() => {
    if (isOpen) loadTags(searchTerm);
  });

  $effect(() => {
    if (disabled) isOpen = false;
  });
</script>

<Filter.Search
  bind:this={searchRef}
  bind:searchTerm
  bind:open={isOpen}
  {disabled}
  {variant}
  autocomplete={true}
  shouldFilter={false}
  hideIcon={true}
  wrap={true}
  rounded="lg"
  placeholder={dynamicPlaceholder}
  onEscape={handleEscape}
  onBackspaceEmpty={handleBackspaceEmpty}
>
  {#snippet leading()}
    {#if !hideIcon}
      <Filter.Icon
        icon={shouldShowLoader ? 'ph:spinner' : 'ph:magnifying-glass'}
        variant="neon"
        spinning={shouldShowLoader}
      />
    {/if}

    {#each selectedTags as tag (tag.id)}
      <TagBadge
        {tag}
        variant="neon"
        showFullPath={true}
        showCount={false}
        onClose={() => removeTag(tag.id)}
      />
    {/each}
  {/snippet}

  {#snippet content()}
    <Filter.Content>
      {#if filteredTags.length > 0}
        <CommandPrimitive.Group class="p-1.5 space-y-0.5">
          {#each filteredTags as tag (tag.id)}
            {@const nested = isTagNested(tag)}
            {@const displayName = nested
              ? getTagLastSegment(tag)
              : getTagDisplayName(tag)}
            {@const parentPath = nested ? getTagParentPath(tag) : ''}
            <Filter.Item value={tag.id} onSelect={() => selectTag(tag)}>
              <Filter.ItemIcon icon="ph:hash" />
              <span class="flex-1 min-w-0 flex flex-col text-left">
                <span class="truncate">{displayName}</span>
                {#if nested && parentPath}
                  <span class="text-[10px] opacity-60 truncate leading-tight">
                    {parentPath}
                  </span>
                {/if}
              </span>
              {#if tag.imageCount > 0}
                <Filter.ItemCount>{tag.imageCount}</Filter.ItemCount>
              {/if}
            </Filter.Item>
          {/each}
        </CommandPrimitive.Group>
      {:else if $isLoadingTags}
        <div class="flex items-center justify-center py-10">
          <Loader variant="minimal" size="sm" />
        </div>
      {:else if searchTerm.trim()}
        <Command.Empty>
          <PickerEmptyState>
            {#snippet icon()}
              <Icon
                icon="ph:magnifying-glass"
                class="w-5 h-5 text-muted-foreground"
              />
            {/snippet}
            {#snippet message()}
              No tags matching "{searchTerm}"
            {/snippet}
          </PickerEmptyState>
        </Command.Empty>
      {:else}
        <Command.Empty>
          <PickerEmptyState>
            {#snippet icon()}
              <Icon icon="ph:tag" class="w-5 h-5 text-muted-foreground" />
            {/snippet}
            {#snippet message()}
              No tags yet
            {/snippet}
          </PickerEmptyState>
        </Command.Empty>
      {/if}
    </Filter.Content>
  {/snippet}
</Filter.Search>
