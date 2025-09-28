<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import {
    type TagFilterContainerVariants,
    tagFilterCheckboxInputVariants,
    tagFilterCheckboxVariants,
    tagFilterClearButtonVariants,
    tagFilterContainerVariants,
    tagFilterContentVariants,
    tagFilterCountBadgeVariants,
    tagFilterDescriptionVariants,
    tagFilterLabelVariants,
    tagFilterTextVariants,
  } from '@slink/feature/Tag';
  import { Checkbox } from '@slink/ui/components/checkbox';

  import Icon from '@iconify/svelte';
  import { slide } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useTagFilterState } from '@slink/lib/state/TagFilterState.svelte';

  interface Props extends TagFilterContainerVariants {
    selectedTags?: Tag[];
    requireAllTags?: boolean;
    onFilterChange?: (tags: Tag[], requireAllTags: boolean) => void;
    onClearFilter?: () => void;
    disabled?: boolean;
    variant?: 'default' | 'neon' | 'minimal';
    compact?: boolean;
  }

  let {
    selectedTags = [],
    requireAllTags = false,
    onFilterChange,
    onClearFilter,
    disabled = false,
    variant = 'neon',
    compact = false,
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

  const matchModeLabel = $derived.by(() =>
    tagFilterState.requireAllTags ? 'Match all tags' : 'Match any tag',
  );

  const matchModeDescription = $derived.by(() =>
    tagFilterState.requireAllTags
      ? 'Items must have every selected tag'
      : 'Items need at least one selected tag',
  );

  const selectedTagsCount = $derived(tagFilterState.selectedTags.length);
  const hasSelectedTags = $derived(selectedTagsCount > 0);

  const containerSize = $derived(compact ? 'sm' : 'md');
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

  {#if hasSelectedTags}
    <div
      class="space-y-3"
      in:slide={{ duration: 300, axis: 'y' }}
      out:slide={{ duration: 200, axis: 'y' }}
    >
      <div
        class={tagFilterContainerVariants({ variant, disabled })}
        onclick={() =>
          !disabled && handleRequireAllChange(!tagFilterState.requireAllTags)}
        role="button"
        tabindex={disabled ? -1 : 0}
        onkeydown={(e) => {
          if (!disabled && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            handleRequireAllChange(!tagFilterState.requireAllTags);
          }
        }}
        aria-label={`Filter mode: ${matchModeLabel}. Click to toggle between match all and match any.`}
      >
        <div class={tagFilterContentVariants({ variant })}>
          <div class={tagFilterCheckboxVariants({ variant })}>
            <Checkbox
              id="require-all-tags"
              checked={tagFilterState.requireAllTags}
              onCheckedChange={handleRequireAllChange}
              {disabled}
              aria-describedby={compact ? undefined : 'filter-mode-description'}
              class={tagFilterCheckboxInputVariants({ variant })}
            />
          </div>

          <div class={tagFilterTextVariants({ variant })}>
            <label
              for="require-all-tags"
              class={tagFilterLabelVariants({ variant, size: containerSize })}
            >
              {matchModeLabel}
              <span class={tagFilterCountBadgeVariants({ variant })}>
                {selectedTagsCount}
              </span>
            </label>
            {#if !compact}
              <p
                id="filter-mode-description"
                class={tagFilterDescriptionVariants({
                  variant,
                  size: containerSize,
                })}
              >
                {matchModeDescription}
              </p>
            {/if}
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button
          type="button"
          class={tagFilterClearButtonVariants({ variant, size: containerSize })}
          onclick={handleClear}
          {disabled}
          aria-label="Remove all selected tags"
          title="Remove all tags"
        >
          <Icon icon="heroicons:x-mark-20-solid" class="w-3.5 h-3.5" />
          {#if !compact}
            <span class="ml-2 text-xs font-medium">Clear</span>
          {/if}
        </button>
      </div>
    </div>
  {/if}
</div>
