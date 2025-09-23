<script lang="ts">
  import { Loader } from '@slink/feature/Layout';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui';

  import {
    createActionIconTheme,
    createActionTheme,
    createButtonTheme,
    createIconTheme,
    createSubtextTheme,
    createTextTheme,
    imageCountTheme,
    tagDropdownTheme,
    tagIconTheme,
    tagItemTheme,
    tagTextTheme,
  } from './TagDropdown.theme';

  interface Props {
    tags: Tag[];
    searchTerm: string;
    selectedIndex: number;
    isLoading: boolean;
    isCreating: boolean;
    canCreate: boolean;
    dropdownId?: string;
    variant?: 'default' | 'neon' | 'minimal';
    onSelectTag: (tag: Tag) => void;
    onCreateTag: (name: string) => void;
  }

  let {
    tags,
    searchTerm,
    selectedIndex,
    isLoading,
    isCreating,
    canCreate,
    dropdownId = 'tag-dropdown',
    variant = 'neon',
    onSelectTag,
    onCreateTag,
  }: Props = $props();

  const createIndex = $derived(tags.length);
  const isCreateSelected = $derived(selectedIndex === createIndex);
</script>

<div id={dropdownId} class={tagDropdownTheme({ variant })}>
  {#if isLoading}
    <div class="flex items-center justify-center py-8">
      <Loader variant="minimal" size="md">
        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">
          Loading tags...
        </span>
      </Loader>
    </div>
  {:else}
    <div
      class="py-2 max-h-64 overflow-y-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-slate-300 hover:scrollbar-thumb-slate-400 dark:scrollbar-thumb-slate-600 dark:hover:scrollbar-thumb-slate-500"
    >
      {#if canCreate && searchTerm.trim()}
        <div class="px-2 pb-2">
          <button
            type="button"
            onclick={() => onCreateTag(searchTerm)}
            disabled={isCreating}
            class={createButtonTheme({
              variant,
              selected: isCreateSelected,
            })}
          >
            <div class="flex-shrink-0">
              <Icon
                icon={isCreating ? 'ph:spinner' : 'ph:plus-circle'}
                class={cn(
                  createIconTheme({ variant }),
                  isCreating && 'animate-spin',
                )}
              />
            </div>

            <div class="flex-1 min-w-0">
              <div class={createTextTheme({ variant })}>
                {isCreating
                  ? `Creating "${searchTerm}"...`
                  : `Create "${searchTerm}"`}
              </div>
              <div class={createSubtextTheme({ variant })}>Add as new tag</div>
            </div>

            <div class="flex-shrink-0">
              <div class={createActionTheme({ variant })}>
                <Icon
                  icon="ph:arrow-right"
                  class={createActionIconTheme({ variant })}
                />
              </div>
            </div>
          </button>
        </div>

        {#if tags.length > 0}
          <div class="mx-2 mb-2">
            <div
              class="h-px bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-700 to-transparent"
            ></div>
          </div>
        {/if}
      {/if}

      {#if tags.length > 0}
        <div class="px-2 space-y-1">
          {#each tags as tag, index (tag.id)}
            <button
              type="button"
              onclick={() => onSelectTag(tag)}
              class={tagItemTheme({
                variant,
                selected: index === selectedIndex,
              })}
            >
              <div class="flex-shrink-0">
                <Icon
                  icon="ph:hash"
                  class={tagIconTheme({
                    variant,
                    selected: index === selectedIndex,
                  })}
                />
              </div>

              <div class="flex-1 min-w-0">
                <div
                  class={tagTextTheme({
                    variant,
                    selected: index === selectedIndex,
                  })}
                >
                  {tag.name}
                </div>
              </div>

              {#if tag.imageCount > 0}
                <div class="flex-shrink-0">
                  <span
                    class={imageCountTheme({
                      variant,
                      selected: index === selectedIndex,
                    })}
                  >
                    {tag.imageCount}
                  </span>
                </div>
              {/if}
            </button>
          {/each}
        </div>
      {:else if searchTerm.trim() && !isLoading && !canCreate}
        <div class="px-6 py-8 text-center">
          <div class="flex flex-col items-center gap-3">
            <div
              class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center"
            >
              <Icon icon="ph:magnifying-glass" class="h-6 w-6 text-slate-400" />
            </div>
            <div class="space-y-1">
              <div
                class="text-sm font-medium text-slate-900 dark:text-slate-100"
              >
                No tags found
              </div>
              <div class="text-xs text-slate-500 dark:text-slate-400">
                No results for "{searchTerm}"
              </div>
            </div>
          </div>
        </div>
      {/if}
    </div>
  {/if}
</div>
