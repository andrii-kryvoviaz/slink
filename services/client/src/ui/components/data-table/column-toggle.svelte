<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Select } from '@slink/ui/components/select';
  import type { Table as TanstackTable } from '@tanstack/table-core';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  interface Props {
    table: TanstackTable<any>;
  }

  let { table }: Props = $props();

  const hidableColumns = $derived(
    table.getAllColumns().filter((col) => col.getCanHide()),
  );

  const columnItems = $derived(
    hidableColumns.map((col) => ({
      value: col.id,
      label: col.columnDef.header?.toString() ?? col.id,
    })),
  );

  const visibleColumnIds = $derived(
    hidableColumns.filter((col) => col.getIsVisible()).map((col) => col.id),
  );

  const handleChange = (values: string[]) => {
    for (const col of hidableColumns) {
      col.toggleVisibility(values.includes(col.id));
    }
  };
</script>

<Select
  type="multiple"
  items={columnItems}
  value={visibleColumnIds}
  onValueChange={handleChange}
  itemClass="capitalize"
  align="end"
>
  {#snippet trigger(props)}
    <Button
      {...props}
      variant="glass"
      size="sm"
      rounded="lg"
      class="gap-2 w-full sm:w-auto"
    >
      <Icon icon="heroicons:adjustments-horizontal" class="size-4" />
      {$t('table.columns')}
      <Icon icon="heroicons:chevron-down" class="size-4" />
    </Button>
  {/snippet}
</Select>
