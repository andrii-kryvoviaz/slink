<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import { Combobox } from '@slink/ui/components/combobox';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { TagDepthIndicator } from '../TagDepthIndicator';

  interface Props {
    tags: Tag[];
    value?: string;
    placeholder?: string;
    onValueChange?: (value: string) => void;
    onSearch?: (query: string) => void;
    loading?: boolean;
    class?: string;
    disabled?: boolean;
    error?: string;
  }

  let {
    tags,
    value = $bindable(),
    placeholder = 'Search tags...',
    onValueChange,
    onSearch,
    loading = false,
    class: className,
    disabled = false,
    error,
  }: Props = $props();

  const comboboxItems = $derived([
    { value: '', label: 'Root Level' },
    ...tags.map((tag) => ({
      value: tag.id,
      label: tag.name,
    })),
  ]);
</script>

<Combobox
  items={comboboxItems}
  bind:value
  {placeholder}
  {onValueChange}
  {onSearch}
  {loading}
  class={className}
  {disabled}
  {error}
  emptyMessage="No tags found."
  itemRenderer={itemRendererSnippet}
/>

{#snippet itemRendererSnippet({
  item,
}: {
  item: { value: string; label: string };
  selected: boolean;
})}
  {#if item.value === ''}
    <Icon icon="lucide:folder" class="mr-2 h-4 w-4" />
    <span>Root Level</span>
  {:else}
    {@const tag = tags.find((t) => t.id === item.value)}
    {#if tag}
      <div class="flex items-center gap-2 min-w-0 flex-1">
        <TagDepthIndicator {tag} />
        <span class="truncate">{item.label}</span>
      </div>
      {#if tag.imageCount > 0}
        <Badge variant="blue" size="sm" class="ml-auto">
          {tag.imageCount}
        </Badge>
      {/if}
    {/if}
  {/if}
{/snippet}
