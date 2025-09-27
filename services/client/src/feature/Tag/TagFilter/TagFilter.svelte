<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { Checkbox } from '@slink/ui/components/checkbox';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useTagFilterState } from '@slink/lib/state/TagFilterState.svelte';

  import {
    type TagFilterContainerVariants,
    tagFilterContainerVariants,
  } from './TagFilter.theme';

  interface Props extends TagFilterContainerVariants {
    selectedTags?: Tag[];
    requireAllTags?: boolean;
    onFilterChange?: (tags: Tag[], requireAllTags: boolean) => void;
    onClearFilter?: () => void;
    disabled?: boolean;
    variant?: 'default' | 'neon' | 'minimal';
  }

  let {
    selectedTags = [],
    requireAllTags = false,
    onFilterChange,
    onClearFilter,
    disabled = false,
    variant = 'default',
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

  const handleRequireAllChange = (requireAll: boolean | 'indeterminate') => {
    const nextValue = requireAll === true;
    tagFilterState.setRequireAllTags(nextValue);
    onFilterChange?.([...tagFilterState.selectedTags], nextValue);
  };

  const handleClear = () => {
    tagFilterState.clear();
    onClearFilter?.();
  };

  const requireAllDescription = $derived.by(() =>
    tagFilterState.requireAllTags
      ? 'Show items that include every selected tag'
      : 'Show items that include at least one of the selected tags',
  );
</script>

<div class="flex flex-col gap-4">
  <TagSelector
    selectedTags={tagFilterState.selectedTags}
    onTagsChange={handleTagsChange}
    placeholder="Search and select tags"
    {variant}
    allowCreate={false}
    {disabled}
  />

  {#if tagFilterState.selectedTags.length > 0}
    <div
      class={tagFilterContainerVariants({ variant, disabled })}
      onclick={() => handleRequireAllChange(!tagFilterState.requireAllTags)}
      role="button"
      tabindex="0"
      onkeydown={(e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          handleRequireAllChange(!tagFilterState.requireAllTags);
        }
      }}
    >
      <div class="flex items-start gap-3">
        <Checkbox
          id="require-all-tags"
          checked={tagFilterState.requireAllTags}
          onCheckedChange={handleRequireAllChange}
          {disabled}
          class="mt-0.5"
        />
        <div class="flex-1 space-y-1">
          <label
            for="require-all-tags"
            class="text-sm font-medium text-foreground cursor-pointer select-none"
          >
            Match all selected tags
          </label>
          <p class="text-xs text-muted-foreground leading-relaxed">
            {requireAllDescription}
          </p>
        </div>
        <Button
          variant="ghost"
          size="sm"
          onclick={(e) => {
            e.stopPropagation();
            handleClear();
          }}
          class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
          {disabled}
        >
          <Icon icon="heroicons:x-mark" class="w-4 h-4" />
          <span class="sr-only">Clear filters</span>
        </Button>
      </div>
    </div>
  {/if}
</div>
