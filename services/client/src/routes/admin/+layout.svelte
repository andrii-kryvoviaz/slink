<script lang="ts">
  interface Props {
    children?: import('svelte').Snippet;
  }

  let { children }: Props = $props();
</script>

<div class="flex h-full w-full flex-col">
  <div class="flex-1">
    {@render children?.()}
  </div>
</div>
