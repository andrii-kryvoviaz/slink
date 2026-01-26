<script lang="ts">
  import {
    PickerItem,
    PickerList,
    type PickerVariant,
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
    showSearch,
    onToggle,
    onCreateNew,
  }: Props = $props();

  const isSelected = (tagId: string) => selectedIds.includes(tagId);
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
  searchPlaceholder="Search tags"
  filterFn={filterTag}
  {onCreateNew}
>
  {#snippet emptyIcon()}
    <Icon icon="ph:tag" class="w-8 h-8" />
  {/snippet}
  {#snippet emptyMessage()}No tags yet{/snippet}
  {#snippet emptyAction()}
    {#if onCreateNew}Create your first tag{/if}
  {/snippet}
  {#snippet createFooter()}
    {#if onCreateNew}New tag{/if}
  {/snippet}
  {#snippet children({ item })}
    {@const tag = item as Tag}
    <PickerItem
      selected={isSelected(tag.id)}
      isToggling={isToggling(tag.id)}
      {disabled}
      {variant}
      color="blue"
      onclick={() => onToggle?.(tag)}
    >
      {#snippet children()}{@html getTagLastSegment(tag)}{/snippet}
      {#snippet subtext()}
        {#if getTagParentPath(tag)}{@html getTagParentPath(tag)}{/if}
      {/snippet}
    </PickerItem>
  {/snippet}
</PickerList>
