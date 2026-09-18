<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';

  import { timeline } from './timeline.theme';

  interface Props {
    class?: string;
    label: Snippet;
    children: Snippet;
  }

  let { class: className, label, children }: Props = $props();

  const theme = timeline();
</script>

<div class={cn(theme.group(), className)}>
  <div class={theme.label()}>{@render label()}</div>
  {@render children()}
</div>
