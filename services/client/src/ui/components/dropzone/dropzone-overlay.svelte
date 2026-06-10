<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { useDropzone } from './context.svelte.js';
  import { dropzoneOverlayTheme } from './dropzone.theme';

  interface Props {
    class?: string;
    children?: Snippet;
  }

  let { class: className, children }: Props = $props();

  const dropzone = useDropzone();
</script>

<div
  class={cn(className, dropzoneOverlayTheme({ visible: dropzone.isDragOver }))}
  role="presentation"
  aria-hidden="true"
>
  {@render children?.()}
</div>
