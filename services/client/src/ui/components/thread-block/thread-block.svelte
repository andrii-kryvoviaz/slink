<script lang="ts" generics="T">
  import * as Collapsible from '@slink/ui/components/collapsible';
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';
  import Icon from '@iconify/svelte';

  import { threadBlock } from './thread-block.theme';

  interface Props {
    latest: T;
    earlier: T[];
    row: Snippet<[T]>;
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

  const theme = threadBlock();
</script>

<Collapsible.Root bind:open class={cn(theme.root(), className)}>
  <div class={theme.row()}>
    {@render row(latest)}
  </div>
  {#if earlier.length > 0}
    <Collapsible.Content class={theme.content()}>
      {#each earlier as item}
        <div class={theme.row()}>
          {@render row(item)}
        </div>
      {/each}
    </Collapsible.Content>
    <Collapsible.Trigger class={theme.trigger()}>
      {@render toggle({ count: earlier.length, open })}
      <Icon icon="lucide:chevron-down" class={theme.chevron()} />
    </Collapsible.Trigger>
  {/if}
</Collapsible.Root>
