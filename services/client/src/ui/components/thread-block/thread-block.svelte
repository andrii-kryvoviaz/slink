<script lang="ts" generics="T">
  import * as Collapsible from '@slink/ui/components/collapsible';
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';
  import Icon from '@iconify/svelte';

  import { type ThreadBlockSurface, threadBlock } from './thread-block.theme';

  interface Props {
    latest?: T;
    earlier: T[];
    row: Snippet<[T, { latest: boolean }]>;
    toggle: Snippet<[{ count: number; open: boolean }]>;
    open?: boolean;
    class?: string;
  }

  let {
    latest,
    earlier,
    row,
    toggle,
    open = $bindable(false),
    class: className,
  }: Props = $props();

  const surface: ThreadBlockSurface = $derived.by(() => {
    if (latest === undefined && !open) return 'bare';

    return 'card';
  });
  const theme = $derived(threadBlock({ surface }));
</script>

<Collapsible.Root bind:open class={cn(theme.root(), className)}>
  {#if latest !== undefined}
    <div class={theme.row()}>
      {@render row(latest, { latest: true })}
    </div>
  {/if}
  {#if earlier.length > 0}
    <Collapsible.Content class={theme.content()}>
      {#each earlier as item}
        <div class={theme.row()}>
          {@render row(item, { latest: false })}
        </div>
      {/each}
    </Collapsible.Content>
    <Collapsible.Trigger class={theme.trigger()}>
      {@render toggle({ count: earlier.length, open })}
      <Icon icon="lucide:chevron-down" class={theme.chevron()} />
    </Collapsible.Trigger>
  {/if}
</Collapsible.Root>
