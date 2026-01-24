<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import TagCreateFooter from '@slink/feature/Tag/TagPicker/TagCreateFooter.svelte';
  import TagEmptyState from '@slink/feature/Tag/TagPicker/TagEmptyState.svelte';
  import {
    type TagPickerVariant,
    tagPickerContainerTheme,
    tagPickerListTheme,
  } from '@slink/feature/Tag/TagPicker/TagPicker.theme';
  import TagPickerItem from '@slink/feature/Tag/TagPicker/TagPickerItem.svelte';
  import TagSearchInput from '@slink/feature/Tag/TagPicker/TagSearchInput.svelte';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tags: Tag[];
    selectedIds?: string[];
    isLoading?: boolean;
    togglingId?: string | null;
    disabled?: boolean;
    variant?: TagPickerVariant;
    showSearch?: boolean;
    onToggle?: (tag: Tag) => void;
    onCreateNew?: () => void;
  }

  let {
    tags,
    selectedIds = [],
    isLoading = false,
    togglingId = null,
    disabled = false,
    variant = 'popover',
    showSearch: showSearchProp,
    onToggle,
    onCreateNew,
  }: Props = $props();

  let searchTerm = $state('');

  const showSearch = $derived(
    showSearchProp !== undefined ? showSearchProp : tags.length > 5,
  );

  const filteredTags = $derived(
    searchTerm
      ? tags.filter((t) =>
          t.path.toLowerCase().includes(searchTerm.toLowerCase()),
        )
      : tags,
  );

  const isSelected = (tagId: string) => selectedIds.includes(tagId);

  const isToggling = (tagId: string) => togglingId === tagId;
</script>

<div class={tagPickerContainerTheme({ variant })}>
  {#if (variant === 'popover' || variant === 'glass') && showSearch}
    <TagSearchInput bind:value={searchTerm} />
  {/if}

  {#if isLoading}
    <div class="flex items-center justify-center py-10">
      <Loader variant="minimal" size="sm" />
    </div>
  {:else if tags.length === 0}
    <TagEmptyState {onCreateNew} />
  {:else}
    <div class="max-h-60 overflow-y-auto">
      <div class={tagPickerListTheme({ variant })}>
        {#if filteredTags.length === 0}
          <div class="flex flex-col items-center gap-2 py-6">
            <Icon
              icon="ph:magnifying-glass"
              class="w-5 h-5 text-gray-400 dark:text-gray-500"
            />
            <p class="text-sm text-gray-500 dark:text-gray-400">
              No matches found
            </p>
          </div>
        {:else}
          {#each filteredTags as tag (tag.id)}
            <TagPickerItem
              {tag}
              selected={isSelected(tag.id)}
              isToggling={isToggling(tag.id)}
              {disabled}
              {variant}
              {onToggle}
            />
          {/each}
        {/if}
      </div>
    </div>

    {#if onCreateNew}
      <TagCreateFooter {onCreateNew} />
    {/if}
  {/if}
</div>
