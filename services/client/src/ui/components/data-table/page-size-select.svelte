<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Select } from '@slink/ui/components/select';

  import Icon from '@iconify/svelte';

  interface Props {
    pageSize: number;
    options: number[];
    onPageSizeChange?: (size: number) => void;
  }

  let { pageSize, options, onPageSizeChange }: Props = $props();

  const items = $derived(
    options.map((s) => ({ value: String(s), label: String(s) })),
  );
</script>

{#if onPageSizeChange}
  <Select
    {items}
    value={String(pageSize)}
    onValueChange={(v: string) => onPageSizeChange?.(Number(v))}
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
        <Icon icon="heroicons:arrows-up-down" class="size-4" />
        Limit {pageSize}
        <Icon icon="heroicons:chevron-down" class="size-4" />
      </Button>
    {/snippet}
  </Select>
{/if}
