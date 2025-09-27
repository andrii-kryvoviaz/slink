<script lang="ts">
  import { Combobox } from '@slink/ui/components/combobox';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui/index.js';

  import { TagDepthIndicator } from '../TagDepthIndicator';

  interface Props {
    tags: Tag[];
    value?: string;
    placeholder?: string;
    onValueChange?: (value: string) => void;
    class?: string;
    disabled?: boolean;
    error?: string;
  }

  let {
    tags,
    value = $bindable(),
    placeholder = 'Search tags...',
    onValueChange,
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
  searchPlaceholder="Search tags..."
  {onValueChange}
  class={className}
  {disabled}
  {error}
  emptyMessage="No tags found."
  itemRenderer={itemRendererSnippet}
/>

{#snippet itemRendererSnippet({
  item,
  selected,
}: {
  item: { value: string; label: string };
  selected: boolean;
})}
  {#if item.value === ''}
    <Icon
      icon="lucide:check"
      class={cn('mr-2 h-4 w-4', !selected && 'text-transparent')}
    />
    <Icon icon="lucide:folder" class="mr-2 h-4 w-4" />
    <span>Root Level</span>
  {:else}
    {@const tag = tags.find((t) => t.id === item.value)}
    {#if tag}
      <Icon
        icon="lucide:check"
        class={cn('mr-2 h-4 w-4', !selected && 'text-transparent')}
      />
      <div class="flex items-center gap-2 min-w-0 flex-1">
        <TagDepthIndicator {tag} />
      </div>
      {#if tag.imageCount > 0}
        <span class="text-xs text-muted-foreground ml-auto">
          ({tag.imageCount})
        </span>
      {/if}
    {/if}
  {/if}
{/snippet}
