<script lang="ts">
  import * as Card from '@slink/ui/components/card';
  import type { Snippet } from 'svelte';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();
</script>

<Card.Title as="h3" size="lg">
  {@render children()}
</Card.Title>
