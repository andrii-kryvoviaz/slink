<script
  lang="ts"
  generics="TData extends import('@tanstack/table-core').RowData"
>
  import { FlexRender } from '@slink/ui/components/data-table';
  import * as Table from '@slink/ui/components/table';
  import type { Table as TanstackTable } from '@tanstack/table-core';
  import type { Snippet } from 'svelte';
  import { tv } from 'tailwind-variants';

  const tableBodyVariants = tv({
    base: 'transition-opacity duration-200',
    variants: {
      loading: {
        true: 'opacity-40 pointer-events-none',
        false: '',
      },
    },
    defaultVariants: {
      loading: false,
    },
  });

  const tableHeadVariants = tv({
    base: 'first:pl-4 first:text-left last:pr-4 last:text-right bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 text-xs font-medium uppercase tracking-wider',
  });

  const tableCellVariants = tv({
    base: 'first:pl-4 first:text-left last:pr-4 last:text-right text-slate-700 dark:text-slate-300',
  });

  interface Props {
    table: TanstackTable<TData>;
    isLoading?: boolean;
    emptyState?: Snippet;
  }

  let { table: dataTable, isLoading = false, emptyState }: Props = $props();
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
                class={tableHeadVariants({
                  class: (header.column.columnDef.meta as any)?.className,
                })}
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
      <Table.Body
        class={tableBodyVariants({
          loading: isLoading && dataTable.getRowModel().rows.length > 0,
        })}
      >
        {#if dataTable.getRowModel().rows.length > 0}
          {#each dataTable.getRowModel().rows as row (row.id)}
            <Table.Row
              class="group/row border-slate-200/60 dark:border-slate-700/40 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-50 dark:hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-700/30 transition-colors duration-200"
            >
              {#each row.getVisibleCells() as cell (cell.id)}
                <Table.Cell
                  class={tableCellVariants({
                    class: (cell.column.columnDef.meta as any)?.className,
                  })}
                >
                  <FlexRender
                    content={cell.column.columnDef.cell}
                    context={cell.getContext()}
                  />
                </Table.Cell>
              {/each}
            </Table.Row>
          {/each}
        {/if}
      </Table.Body>
    </Table.Root>
    {#if !dataTable.getRowModel().rows.length && !isLoading && emptyState}
      <div class="flex items-center justify-center py-16">
        {@render emptyState()}
      </div>
    {/if}
  </div>
</div>
