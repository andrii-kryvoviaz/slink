<script lang="ts">
  import type { Snippet } from 'svelte';

  import { setShareControls } from '../State/Context';
  import type { ShareState } from '../State/State.svelte';

  interface Props {
    state: ShareState;
    children: Snippet;
  }

  let { state, children }: Props = $props();

  setShareControls(state);
</script>

{@render children()}
