<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useTagFilterState } from '@slink/lib/state/TagFilterState.svelte';

  interface Props {
    selectedTags?: Tag[];
    requireAllTags?: boolean;
    onFilterChange?: (tags: Tag[], requireAllTags: boolean) => void;
    disabled?: boolean;
    variant?: 'default' | 'neon' | 'minimal';
  }

  let {
    selectedTags = [],
    requireAllTags = false,
    onFilterChange,
    disabled = false,
    variant = 'neon',
  }: Props = $props();

  const tagFilterState = useTagFilterState();

  $effect(() => {
    tagFilterState.syncFromExternal(selectedTags, requireAllTags);
  });

  const handleTagsChange = (tags: Tag[]) => {
    tagFilterState.setSelectedTags(tags);
    onFilterChange?.(
      [...tagFilterState.selectedTags],
      tagFilterState.requireAllTags,
    );
  };
</script>

<TagSelector
  selectedTags={tagFilterState.selectedTags}
  onTagsChange={handleTagsChange}
  placeholder="Search and select tags"
  {variant}
  allowCreate={false}
  {disabled}
/>
