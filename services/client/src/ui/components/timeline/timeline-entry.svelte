<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';
  import type { HTMLAttributes } from 'svelte/elements';

  import { timeline } from './timeline.theme';

  interface Props extends HTMLAttributes<HTMLDivElement> {
    active: boolean;
    class?: string;
    children: Snippet;
  }

  let { active, class: className, children, ...rest }: Props = $props();

  const theme = $derived(timeline({ active }));
</script>

<div class={cn(theme.entry(), className)} {...rest}>
  <div aria-hidden="true" class={theme.dotCell()}>
    <div class={theme.dot()}></div>
  </div>
  <div class={theme.content()}>{@render children()}</div>
</div>
