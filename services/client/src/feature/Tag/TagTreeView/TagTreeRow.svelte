<script lang="ts">
  import TagActionsCell from '@slink/feature/Tag/TagDataTable/TagActionsCell.svelte';
  import TagCountCell from '@slink/feature/Tag/TagDataTable/TagCountCell.svelte';
  import TagNameCell from '@slink/feature/Tag/TagDataTable/TagNameCell.svelte';
  import * as Table from '@slink/ui/components/table';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type {
    ColumnClassNames,
    ColumnVisibility,
    TreeRow,
  } from './types';

  interface Props {
    row: TreeRow;
    isExpanded: boolean;
    onToggle: (id: string) => void;
    onDelete: (tag: Tag) => Promise<void>;
    columnVisibility: ColumnVisibility;
    columnClassNames: ColumnClassNames;
  }

  let {
    row,
    isExpanded,
    onToggle,
    onDelete,
    columnVisibility,
    columnClassNames,
  }: Props = $props();

  const isColumnVisible = (id: keyof ColumnVisibility) => {
    return columnVisibility?.[id] ?? false;
  };

  const getColumnClassName = (id: keyof ColumnClassNames) => {
    return columnClassNames?.[id] ?? '';
  };
</script>

<Table.Row
  class="group/row border-slate-200/60 dark:border-slate-700/40 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-50 dark:hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-700/30 transition-colors duration-200"
>
  {#if isColumnVisible('name')}
    <Table.Cell
      class="{getColumnClassName('name')} text-slate-700 dark:text-slate-300"
    >
      <div
        class="flex items-center gap-2 min-w-0"
        style:padding-left={`${row.depth * 20}px`}
      >
        {#if row.node.hasChildren === false}
          <span class="h-6 w-6"></span>
        {:else}
          <button
            type="button"
            onclick={() => onToggle(row.node.tag.id)}
            class="flex h-6 w-6 items-center justify-center rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/50 transition-colors disabled:cursor-not-allowed"
            aria-label={isExpanded
              ? `Collapse ${row.node.tag.name}`
              : `Expand ${row.node.tag.name}`}
            disabled={row.node.isLoading}
          >
            {#if row.node.isLoading}
              <Icon icon="lucide:loader-2" class="h-4 w-4 animate-spin" />
            {:else}
              <Icon
                icon="lucide:chevron-right"
                class={`h-4 w-4 transition-transform duration-200 ${
                  isExpanded ? 'rotate-90' : ''
                }`}
              />
            {/if}
          </button>
        {/if}
        <div class="min-w-0 flex-1">
          <TagNameCell tag={row.node.tag} />
        </div>
      </div>
    </Table.Cell>
  {/if}

  {#if isColumnVisible('imageCount')}
    <Table.Cell
      class="{getColumnClassName('imageCount')} text-slate-700 dark:text-slate-300"
    >
      <TagCountCell
        count={row.node.tag.imageCount}
        type="images"
        tag={row.node.tag}
      />
    </Table.Cell>
  {/if}

  {#if isColumnVisible('children')}
    <Table.Cell
      class="{getColumnClassName('children')} text-slate-700 dark:text-slate-300"
    >
      <TagCountCell
        count={row.node.children.length}
        type="children"
        tag={row.node.tag}
      />
    </Table.Cell>
  {/if}

  {#if isColumnVisible('actions')}
    <Table.Cell
      class="{getColumnClassName('actions')} text-slate-700 dark:text-slate-300"
    >
      <TagActionsCell tag={row.node.tag} {onDelete} />
    </Table.Cell>
  {/if}
</Table.Row>
