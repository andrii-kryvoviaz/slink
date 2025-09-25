<script lang="ts">
  import { TagPill } from '@slink/feature/Tag';
  import { TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { Checkbox } from '@slink/ui/components/checkbox';
  import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
  } from '@slink/ui/components/collapsible';
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { useTagFilterState } from '@slink/lib/state/TagFilterState.svelte';

  interface Props {
    selectedTags?: Tag[];
    requireAllTags?: boolean;
    onFilterChange?: (tags: Tag[], requireAllTags: boolean) => void;
    onClearFilter?: () => void;
    disabled?: boolean;
  }

  let {
    selectedTags = [],
    requireAllTags = false,
    onFilterChange,
    onClearFilter,
    disabled = false,
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

  const hasActiveFilter = $derived(tagFilterState.selectedTags.length > 0);
  const filterButtonLabel = $derived.by(() =>
    hasActiveFilter ? 'Tag filters' : 'Filter by tags',
  );
  const filterDescription = $derived.by(() => {
    if (!hasActiveFilter) {
      return 'Find items by selecting the tags you care about';
    }
    if (tagFilterState.selectedTags.length === 1) {
      return `Filtering by "${tagFilterState.selectedTags[0].name}" tag`;
    }
    const mode = tagFilterState.requireAllTags ? 'all' : 'any';
    return `Filtering by ${mode} of ${tagFilterState.selectedTags.length} tags`;
  });
  const requireAllDescription = $derived.by(() =>
    tagFilterState.requireAllTags
      ? 'Show items that include every selected tag'
      : 'Show items that include at least one of the selected tags',
  );
  const filterSummary = $derived.by(() => {
    if (!hasActiveFilter) {
      return '';
    }
    if (tagFilterState.selectedTags.length === 1) {
      return `Filtering by "${tagFilterState.selectedTags[0].name}" tag`;
    }
    const mode = tagFilterState.requireAllTags ? 'all' : 'any';
    return `Filtering by ${mode} of ${tagFilterState.selectedTags.length} tags`;
  });
</script>

<div class="flex flex-col gap-4">
  <div class="flex flex-col gap-3">
    <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3">
      <Collapsible
        bind:open={tagFilterState.isExpanded}
        class="flex-1 min-w-[16rem] sm:min-w-[18rem] max-w-full"
      >
        <CollapsibleTrigger aria-label={filterButtonLabel}>
          <Button
            variant="outline"
            size="sm"
            class="w-full sm:w-auto gap-2 px-3 h-9 border-slate-200 dark:border-slate-700 data-[state=open]:bg-slate-100 dark:data-[state=open]:bg-slate-800"
            {disabled}
          >
            <Icon
              icon="heroicons:funnel"
              class="w-4 h-4 text-slate-500 dark:text-slate-400"
            />
            <span
              class="text-sm font-medium text-slate-700 dark:text-slate-300"
            >
              {filterButtonLabel}
            </span>
            {#if hasActiveFilter}
              <span
                class="flex items-center justify-center w-5 h-5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full text-xs font-semibold"
              >
                {tagFilterState.selectedTags.length}
              </span>
            {/if}
            <Icon
              icon="heroicons:chevron-down"
              class={`w-4 h-4 text-slate-400 transition-transform duration-200${tagFilterState.isExpanded ? ' rotate-180' : ''}`}
            />
          </Button>
        </CollapsibleTrigger>

        <CollapsibleContent class="mt-3">
          <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
              <div class="flex flex-col gap-1">
                <h3
                  class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                >
                  Select tags
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  {filterDescription}
                </p>
              </div>

              <TagSelector
                selectedTags={tagFilterState.selectedTags}
                onTagsChange={handleTagsChange}
                placeholder="Search and select tags"
                variant="minimal"
                allowCreate={false}
                {disabled}
              />
            </div>

            {#if tagFilterState.selectedTags.length > 0}
              <div
                class="flex items-start gap-3 rounded-md border border-slate-200 dark:border-slate-700 px-3 py-3"
              >
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
                    class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer select-none"
                  >
                    Match all selected tags
                  </label>
                  <p
                    class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed"
                  >
                    {requireAllDescription}
                  </p>
                </div>
              </div>
            {/if}
          </div>
        </CollapsibleContent>
      </Collapsible>

      {#if hasActiveFilter}
        <Tooltip>
          {#snippet trigger()}
            <Button
              variant="ghost"
              size="sm"
              onclick={handleClear}
              class="h-9 px-3 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
              {disabled}
            >
              <Icon icon="heroicons:x-mark" class="w-4 h-4" />
              <span>Clear</span>
            </Button>
          {/snippet}
          Remove all tag filters
        </Tooltip>
      {/if}
    </div>
  </div>

  {#if hasActiveFilter}
    <div
      class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-950/40 px-4 py-3"
    >
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-start gap-2">
          <Icon
            icon="heroicons:funnel"
            class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0"
          />
          <div class="space-y-1">
            <h4
              class="text-sm font-semibold text-indigo-900 dark:text-indigo-100"
            >
              Active filters
            </h4>
            <p class="text-xs text-indigo-700 dark:text-indigo-300">
              {filterSummary}
            </p>
          </div>
        </div>
        <Tooltip>
          {#snippet trigger()}
            <Button
              variant="ghost"
              size="sm"
              onclick={handleClear}
              class="h-9 w-9 p-0 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
              {disabled}
            >
              <Icon icon="heroicons:x-mark" class="w-4 h-4" />
              <span class="sr-only">Clear all filters</span>
            </Button>
          {/snippet}
          Clear all filters
        </Tooltip>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        {#each tagFilterState.selectedTags as tag (tag.id)}
          <TagPill
            {tag}
            size="sm"
            variant="neon"
            onRemove={() => {
              const newTags = tagFilterState.selectedTags.filter(
                (t) => t.id !== tag.id,
              );
              handleTagsChange(newTags);
            }}
            showImageCount={false}
            removable
          />
        {/each}

        {#if tagFilterState.requireAllTags}
          <span
            class="flex items-center gap-1.5 rounded-md border border-amber-200 dark:border-amber-700 bg-amber-100 dark:bg-amber-900/30 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-300"
          >
            <Icon icon="heroicons:check-circle" class="w-3.5 h-3.5" />
            <span>All required</span>
          </span>
        {/if}
      </div>
    </div>
  {/if}
</div>
