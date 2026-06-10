<script lang="ts">
  import type { Snippet } from 'svelte';

  import { type FileDropState, setDropzone } from './context.svelte.js';

  interface Props {
    state: FileDropState;
    children?: Snippet;
  }

  let { state, children }: Props = $props();

  setDropzone(state);
</script>

{@render children?.()}
