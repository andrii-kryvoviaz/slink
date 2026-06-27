<script lang="ts">
  import {
    type PickerCreate,
    PickerItem,
    PickerList,
    type PickerVariant,
    type SelectionState,
    createSelectionResolver,
  } from '@slink/ui/components/picker';

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
    create?: PickerCreate;
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
    create,
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
  searchPlaceholder="Search"
  filterFn={filterTag}
  {create}
>
  {#snippet emptyIcon()}
    <Icon icon="ph:tag" class="h-[18px] w-[18px]" />
  {/snippet}
  {#snippet emptyMessage()}No tags yet{/snippet}
  {#snippet emptyDescription()}Label uploads to find them later{/snippet}
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
      {#snippet children()}{getTagLastSegment(tag)}{/snippet}
      {#snippet subtext()}
        {getTagParentPath(tag) || 'Root'}
      {/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
