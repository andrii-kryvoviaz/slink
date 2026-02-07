<script
  lang="ts"
  generics="TData extends import('@tanstack/table-core').RowData"
>
  import { FlexRender } from '@slink/ui/components/data-table';
  import * as Table from '@slink/ui/components/table';
  import type { ColumnDef, Table as TanstackTable } from '@tanstack/table-core';
  import type { Snippet } from 'svelte';

  interface Props {
    table: TanstackTable<TData>;
    columns: ColumnDef<TData>[];
    isLoading?: boolean;
    emptyState?: Snippet;
    loadingState?: Snippet;
  }

  let {
    table: dataTable,
    columns,
    isLoading = false,
    emptyState,
    loadingState,
  }: Props = $props();
</script>

<div
  class="flex-1 overflow-hidden rounded-xl border border-slate-200/60 dark:border-slate-700/40 bg-white dark:bg-slate-800/30"
>
  <div class="overflow-x-auto">
    <Table.Root>
      <Table.Header>
        {#each dataTable.getHeaderGroups() as headerGroup (headerGroup.id)}
          <Table.Row
            class="border-slate-200/60 dark:border-slate-700/40 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-transparent"
          >
            {#each headerGroup.headers as header (header.id)}
              <Table.Head
                class="{(header.column.columnDef.meta as any)
                  ?.className} bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 text-xs font-medium uppercase tracking-wider"
              >
                {#if !header.isPlaceholder}
                  <FlexRender
                    content={header.column.columnDef.header}
                    context={header.getContext()}
                  />
                {/if}
              </Table.Head>
            {/each}
          </Table.Row>
        {/each}
      </Table.Header>
      <Table.Body>
        {#if isLoading && loadingState}
          <Table.Row
            class="border-slate-200/60 dark:border-slate-700/40 hover:bg-transparent"
          >
            <Table.Cell colspan={columns.length} class="h-32 text-center">
              {@render loadingState()}
            </Table.Cell>
          </Table.Row>
        {:else if dataTable.getRowModel().rows.length > 0}
          {#each dataTable.getRowModel().rows as row (row.id)}
            <Table.Row
              class="group/row border-slate-200/60 dark:border-slate-700/40 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-50 dark:hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-700/30 transition-colors duration-200"
            >
              {#each row.getVisibleCells() as cell (cell.id)}
                <Table.Cell
                  class="{(cell.column.columnDef.meta as any)
                    ?.className} text-slate-700 dark:text-slate-300"
                >
                  <FlexRender
                    content={cell.column.columnDef.cell}
                    context={cell.getContext()}
                  />
                </Table.Cell>
              {/each}
            </Table.Row>
          {/each}
        {:else if emptyState}
          <Table.Row
            class="border-slate-200/60 dark:border-slate-700/40 hover:bg-transparent"
          >
            <Table.Cell colspan={columns.length} class="h-32 text-center">
              {@render emptyState()}
            </Table.Cell>
          </Table.Row>
        {:else}
          <Table.Row
            class="border-slate-200/60 dark:border-slate-700/40 hover:bg-transparent"
          >
            <Table.Cell colspan={columns.length} class="h-32 text-center">
              <p class="text-sm text-slate-500 dark:text-slate-400">No data</p>
            </Table.Cell>
          </Table.Row>
        {/if}
      </Table.Body>
    </Table.Root>
  </div>
</div>
