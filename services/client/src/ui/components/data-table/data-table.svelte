<script
  lang="ts"
  generics="TData extends import('@tanstack/table-core').RowData"
>
  import type { DataTableFeatures } from '@slink/ui/components/data-table';
  import * as Table from '@slink/ui/components/table';
  import {
    FlexRender,
    type Table as TanstackTable,
  } from '@tanstack/svelte-table';
  import type { Snippet } from 'svelte';
  import { tv } from 'tailwind-variants';

  import { cn } from '@slink/utils/ui';

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
    base: 'first:pl-4 first:text-left last:pr-4 last:text-right bg-muted-soft text-foreground-muted text-xs font-medium uppercase tracking-wider',
  });

  const tableCellVariants = tv({
    base: 'first:pl-4 first:text-left last:pr-4 last:text-right text-foreground-soft',
  });

  interface Props {
    table: TanstackTable<DataTableFeatures, TData>;
    isLoading?: boolean;
    emptyState?: Snippet;
    onRowClick?: (row: any) => void;
    rowClass?: (row: NoInfer<TData>) => string;
  }

  let {
    table: dataTable,
    isLoading = false,
    emptyState,
    onRowClick,
    rowClass,
  }: Props = $props();
</script>

<div
  class="flex-1 overflow-hidden rounded-xl border border-border/60 bg-card dark:bg-muted/30"
>
  <div class="@container overflow-x-auto">
    <Table.Root>
      <Table.Header>
        {#each dataTable.getHeaderGroups() as headerGroup (headerGroup.id)}
          <Table.Row
            class="border-border/60 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-transparent"
          >
            {#each headerGroup.headers as header (header.id)}
              <Table.Head
                class={tableHeadVariants({
                  class: header.column.columnDef.meta?.className,
                })}
              >
                {#if !header.isPlaceholder}
                  <FlexRender {header} />
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
              class={cn(
                'group/row border-border/60 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-muted-soft transition-colors duration-200',
                onRowClick && 'cursor-pointer',
                rowClass?.(row.original),
              )}
              onclick={onRowClick ? () => onRowClick(row.original) : undefined}
            >
              {#each row.getVisibleCells() as cell (cell.id)}
                <Table.Cell
                  class={tableCellVariants({
                    class: cell.column.columnDef.meta?.className,
                  })}
                >
                  <FlexRender {cell} />
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
