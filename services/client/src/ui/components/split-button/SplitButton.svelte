<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import type { Snippet } from 'svelte';

  import {
    type SplitButtonVariants,
    splitButtonVariants,
  } from './split-button.variants';

  let {
    onclick,
    class: customClass,
    children,
    aside: asideSnippet,
    asidePosition = 'end',
    size = 'xs',
    rounded = 'md',
  }: {
    onclick: () => void;
    class?: string;
    children: Snippet;
    aside: Snippet;
    asidePosition?: SplitButtonVariants['asidePosition'];
    size?: SplitButtonVariants['size'];
    rounded?: SplitButtonVariants['rounded'];
  } = $props();

  const {
    wrapper,
    label,
    aside: asideSlot,
  } = splitButtonVariants({ asidePosition, size, rounded });
</script>

<Button
  variant="toggle"
  size="xs"
  {rounded}
  class="group p-0.5 [&>div]:!p-0 active:scale-[0.97] transition-all duration-150 {customClass}"
  {onclick}
>
  <div class={wrapper()}>
    <span class={label()}>
      {@render children()}
    </span>
    <span class={asideSlot()}>
      {@render asideSnippet()}
    </span>
  </div>
</Button>
