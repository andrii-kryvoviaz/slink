<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';

  import { timeline } from './timeline.theme';

  interface Props {
    class?: string;
    children: Snippet;
  }

  let { class: className, children }: Props = $props();

  const theme = timeline();
</script>

<div class={cn(theme.root(), className)}>
  {@render children()}
</div>
