<script lang="ts" generics="T">
  import * as Collapsible from '@slink/ui/components/collapsible';
  import { type Snippet, tick } from 'svelte';

  import { plural } from '$lib/utils/i18n';
  import { cn } from '$lib/utils/ui';
  import Icon from '@iconify/svelte';

  import { threadBlock } from './thread-block.theme';
  import { ThreadReveal } from './thread-reveal.svelte';

  interface Props {
    header?: Snippet;
    latest?: T;
    earlier: T[];
    row: Snippet<[T, { latest: boolean }]>;
    toggle: Snippet<[{ count: number; open: boolean }]>;
    open?: boolean;
    pageSize?: number;
    class?: string;
  }

  let {
    header,
    latest,
    earlier,
    row,
    toggle,
    open = $bindable(false),
    pageSize = 8,
    class: className,
  }: Props = $props();

  let panel: HTMLElement | null = $state(null);
  let trigger: HTMLElement | null = $state(null);

  const reveal = new ThreadReveal(
    () => pageSize,
    () => earlier.length,
  );

  const theme = $derived(threadBlock({ latest: latest !== undefined }));

  function handleOpenChangeComplete(value: boolean) {
    if (value) return;

    reveal.reset();
  }

  async function handleShowMore() {
    const firstRevealed = reveal.visible;
    reveal.showMore();
    await tick();

    const target = panel?.children
      .item(firstRevealed)
      ?.querySelector<HTMLElement>(
        'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])',
      );
    if (target) {
      target.focus();
      return;
    }
    if (reveal.remaining === 0) trigger?.focus();
  }
</script>

<Collapsible.Root
  bind:open
  onOpenChangeComplete={handleOpenChangeComplete}
  class={cn(theme.root(), className)}
>
  {#if header}
    {@render header()}
  {/if}
  {#if latest !== undefined}
    <div class={theme.row()}>
      {@render row(latest, { latest: true })}
    </div>
  {/if}
  {#if earlier.length > 0}
    <Collapsible.Trigger bind:ref={trigger} class={theme.trigger()}>
      {@render toggle({ count: earlier.length, open })}
      <Icon icon="lucide:chevron-down" class={theme.chevron()} />
    </Collapsible.Trigger>
    <Collapsible.Content class={theme.content()}>
      <div bind:this={panel} class={theme.panel()}>
        {#each earlier.slice(0, reveal.visible) as item}
          <div class={theme.row()}>
            {@render row(item, { latest: false })}
          </div>
        {/each}
        {#if reveal.remaining > 0}
          <button type="button" class={theme.more()} onclick={handleShowMore}>
            {plural(reveal.next, ['Show # more', 'Show # more'])}
          </button>
        {/if}
      </div>
    </Collapsible.Content>
  {/if}
</Collapsible.Root>
