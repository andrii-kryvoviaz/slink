<script lang="ts">
  import * as Table from '@slink/ui/components/table';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import TagTreeRow from './TagTreeRow.svelte';
  import type {
    ColumnClassNames,
    ColumnVisibility,
  } from './types';
  import { useTagTree } from './useTagTree.svelte';

  interface Props {
    onDelete: (tag: Tag) => Promise<void>;
    columnVisibility?: ColumnVisibility;
    columnClassNames?: ColumnClassNames;
    columnCount?: number;
    refreshKey?: string | number;
  }

  let {
    onDelete,
    columnVisibility = {
      name: true,
      imageCount: true,
      children: true,
      actions: true,
    },
    columnClassNames = {},
    columnCount = 1,
    refreshKey,
  }: Props = $props();

  let lastRefreshKey = $state<unknown>(Symbol('tree-init'));

  const tree = useTagTree();

  $effect(() => {
    if (refreshKey === lastRefreshKey) {
      return;
    }

    lastRefreshKey = refreshKey;
    tree.loadRootTags();
  });
</script>

{#if tree.isLoadingRoots && tree.rootIds.length === 0}
  <Table.Row class="border-slate-200/60 dark:border-slate-700/40">
    <Table.Cell colspan={columnCount} class="h-32 text-center">
      <div class="flex flex-col items-center gap-3">
        <Icon
          icon="lucide:loader-2"
          class="h-6 w-6 text-slate-400 dark:text-slate-500 animate-spin"
        />
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Loading tags...
        </p>
      </div>
    </Table.Cell>
  </Table.Row>
{:else if tree.rootError}
  <Table.Row class="border-slate-200/60 dark:border-slate-700/40">
    <Table.Cell colspan={columnCount} class="h-32 text-center">
      <div class="flex flex-col items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <Icon icon="lucide:alert-circle" class="h-5 w-5" />
        <p>{tree.rootError}</p>
      </div>
    </Table.Cell>
  </Table.Row>
{:else if tree.treeRows.length > 0}
  {#each tree.treeRows as row (row.node.tag.id)}
    <TagTreeRow
      {row}
      isExpanded={tree.expandedTagIds.has(row.node.tag.id)}
      onToggle={tree.toggleNode}
      {onDelete}
      columnVisibility={columnVisibility}
      columnClassNames={columnClassNames}
    />
  {/each}
{:else}
  <Table.Row class="border-slate-200/60 dark:border-slate-700/40">
    <Table.Cell colspan={columnCount} class="h-32 text-center">
      <div class="flex flex-col items-center gap-3 py-8">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
        >
          <Icon
            icon="lucide:tag"
            class="h-6 w-6 text-slate-400 dark:text-slate-500"
          />
        </div>
        <div class="space-y-1">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
            No tags found
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Create your first tag to get started
          </p>
        </div>
      </div>
    </Table.Cell>
  </Table.Row>
{/if}
