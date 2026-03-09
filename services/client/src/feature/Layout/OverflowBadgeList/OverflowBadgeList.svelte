<script lang="ts" generics="T extends { id: string }">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import { type Snippet } from 'svelte';

  import { pluralize } from '@slink/lib/utils/string/pluralize';

  interface Props {
    items: T[];
    maxVisible?: number;
    itemLabel: string;
    badge: Snippet<[T]>;
    overflowBadge?: Snippet<[T]>;
  }

  let { items, maxVisible, itemLabel, badge, overflowBadge }: Props = $props();

  const visibleItems = $derived(
    maxVisible !== undefined ? items.slice(0, maxVisible) : items,
  );

  const overflowItems = $derived(
    maxVisible !== undefined ? items.slice(maxVisible) : [],
  );

  const overflowCount = $derived(overflowItems.length);
</script>

<div class="flex flex-wrap gap-2">
  {#each visibleItems as item (item.id)}
    {@render badge(item)}
  {/each}

  {#if overflowCount > 0}
    <HoverCard.Root openDelay={300} closeDelay={100}>
      <HoverCard.Trigger class="cursor-pointer">
        <Badge variant="default" size="sm">+{overflowCount}</Badge>
      </HoverCard.Trigger>
      <HoverCard.Content side="bottom" align="start" variant="glass" size="sm">
        <p class="text-xs text-muted-foreground mb-2">
          {pluralize(overflowCount, itemLabel)}
        </p>
        <div class="flex flex-wrap gap-2">
          {#each overflowItems as item (item.id)}
            {@render (overflowBadge ?? badge)(item)}
          {/each}
        </div>
      </HoverCard.Content>
    </HoverCard.Root>
  {/if}
</div>
