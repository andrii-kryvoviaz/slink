<script lang="ts">
  import type { Snippet } from 'svelte';

  import { fade, fly } from 'svelte/transition';

  import type { SelectionState } from '@slink/lib/state/SelectionState.svelte';

  import { Key, cn } from '@slink/utils/ui';

  interface Props {
    id: string;
    selectionState?: SelectionState;
    onSelectionChange?: (id: string) => void;
    cardClass: (isSelected: boolean) => string;
    flyDelay?: number;
    children: Snippet<[boolean]>;
  }

  let {
    id,
    selectionState,
    onSelectionChange,
    cardClass,
    flyDelay,
    children,
  }: Props = $props();

  const isSelected = $derived(selectionState?.isSelected(id) ?? false);

  const select = (e: Event) => {
    e.preventDefault();
    if (!selectionState) return;

    selectionState.select(id);
    onSelectionChange?.(id);
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key !== Key.Enter) return;
    select(e);
  };

  const articleProps = $derived({
    class: cn(cardClass(isSelected), 'cursor-pointer'),
    onclick: select,
    onkeydown: handleKeydown,
    role: 'button' as const,
    tabindex: 0,
  });
</script>

{#if flyDelay !== undefined}
  <article
    in:fly={{ y: 20, duration: 300, delay: flyDelay }}
    out:fade={{ duration: 200 }}
    {...articleProps}
  >
    {@render children(isSelected)}
  </article>
{:else}
  <article {...articleProps}>
    {@render children(isSelected)}
  </article>
{/if}
