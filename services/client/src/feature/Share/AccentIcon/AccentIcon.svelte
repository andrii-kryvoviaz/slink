<script lang="ts">
  import type { Snippet } from 'svelte';

  import { accentIcon } from './AccentIcon.theme';

  interface Props {
    size?: 'md' | 'lg';
    children: Snippet;
  }

  let { size = 'md', children }: Props = $props();
</script>

<div class={accentIcon({ size })}>
  {@render children()}
</div>
