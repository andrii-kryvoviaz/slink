<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    children: Snippet;
    class?: string;
  }

  let { children, class: className = '' }: Props = $props();

  const handleEvent = (e: Event) => {
    e.stopPropagation();
  };
</script>

<span
  role="presentation"
  onclick={handleEvent}
  onkeydown={handleEvent}
  class={className}
>
  {@render children()}
</span>
