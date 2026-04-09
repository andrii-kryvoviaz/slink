<script lang="ts">
  import {
    PickerItem,
    PickerList,
    type PickerVariant,
    type SelectionState,
    createSelectionResolver,
  } from '@slink/ui/components/picker';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagLastSegment, getTagParentPath } from '@slink/lib/utils/tag';

  interface Props {
    tags: Tag[];
    selectedIds?: string[];
    isLoading?: boolean;
    togglingId?: string | null;
    disabled?: boolean;
    variant?: PickerVariant;
    showSearch?: boolean;
    getItemState?: (id: string) => SelectionState;
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
    getItemState,
    showSearch,
    onToggle,
    onCreateNew,
  }: Props = $props();

  const resolveSelected = $derived(
    createSelectionResolver(selectedIds, getItemState),
  );
  const isToggling = (tagId: string) => togglingId === tagId;

  const filterTag = (tag: Tag, searchTerm: string) =>
    tag.path.toLowerCase().includes(searchTerm.toLowerCase());
</script>

<PickerList
  items={tags}
  {variant}
  color="blue"
  {isLoading}
  {showSearch}
  searchPlaceholder={$t('tag.picker.search_placeholder')}
  filterFn={filterTag}
  {onCreateNew}
>
  {#snippet emptyIcon()}
    <Icon icon="ph:tag" class="w-5 h-5 text-gray-400 dark:text-gray-500" />
  {/snippet}
  {#snippet emptyMessage()}{$t('tag.picker.no_tags_yet')}{/snippet}
  {#snippet emptyAction()}
    {#if onCreateNew}{$t('tag.picker.create_first_tag')}{/if}
  {/snippet}
  {#snippet createFooter()}
    {#if onCreateNew}{$t('tag.picker.new_tag')}{/if}
  {/snippet}
  {#snippet children({ item, highlighted })}
    {@const tag = item as Tag}
    <PickerItem
      selected={resolveSelected(tag.id)}
      isToggling={isToggling(tag.id)}
      {disabled}
      {variant}
      color="blue"
      {highlighted}
      onclick={() => onToggle?.(tag)}
    >
      {#snippet children()}{@html getTagLastSegment(tag)}{/snippet}
      {#snippet subtext()}
        {@html getTagParentPath(tag) || $t('tag.picker.root')}
      {/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
