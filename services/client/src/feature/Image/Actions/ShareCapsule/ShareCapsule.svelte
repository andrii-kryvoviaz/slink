<script lang="ts">
  import type { Snippet } from 'svelte';

  import { shareCapsuleVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();

  const context = getImageActionsContext();
  const capsule = $derived(shareCapsuleVariants({ layout: context.layout }));
</script>

<div class={capsule.capsule()}>
  {@render children()}
</div>
