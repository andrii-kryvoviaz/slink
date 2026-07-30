<script lang="ts">
  import type { Snippet } from 'svelte';

  import { toolbarTierVariants } from './toolbar.theme';

  interface Props {
    full: Snippet<[boolean]>;
    compact: Snippet<[boolean]>;
    compactActive?: boolean;
    anchor?: HTMLElement;
  }

  let {
    full,
    compact,
    compactActive = $bindable(false),
    anchor = $bindable(),
  }: Props = $props();

  const tiers = toolbarTierVariants();

  $effect(() => {
    if (!anchor) return;

    const observer = new ResizeObserver(([entry]) => {
      compactActive = entry.contentRect.width > 0;
    });
    observer.observe(anchor);

    return () => observer.disconnect();
  });
</script>

<div>
  <div class={tiers.full()}>
    {@render full(!compactActive)}
  </div>
  <div class={tiers.compact()} bind:this={anchor}>
    {@render compact(compactActive)}
  </div>
</div>
