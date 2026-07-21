<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { getToolbarContext, setToolbarContext } from './context';
  import { toolbarGroupVariants } from './toolbar.theme';

  interface Props {
    class?: string;
    children: Snippet;
  }

  let { class: className, children }: Props = $props();

  const parent = getToolbarContext();
  const surface = parent?.surface ?? 'floating';

  setToolbarContext({
    tone: parent?.tone ?? 'dark',
    surface,
    withinRoot: parent?.withinRoot ?? false,
  });
</script>

<div class={cn(toolbarGroupVariants({ surface }), className)}>
  {@render children()}
</div>
