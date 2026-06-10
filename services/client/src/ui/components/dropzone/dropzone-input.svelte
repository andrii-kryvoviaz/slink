<script lang="ts">
  import type { HTMLInputAttributes } from 'svelte/elements';

  import { cn } from '@slink/utils/ui/index.js';

  import { useDropzone } from './context.svelte.js';
  import { dropzoneInputTheme } from './dropzone.theme';

  interface Props extends HTMLInputAttributes {
    class?: string;
  }

  let { class: className, children, ...restProps }: Props = $props();

  const dropzone = useDropzone();

  let input: HTMLInputElement | undefined = $state();

  function keydown(ev: KeyboardEvent) {
    if (!input) return;

    if ([' ', 'Enter'].includes(ev.key)) {
      ev.preventDefault();
      input.click();
    }
  }

  function onClick(event: MouseEvent) {
    if (!input) return;

    event.preventDefault();
    input.click();
  }
</script>

<button
  class={cn(dropzoneInputTheme(), className)}
  onkeydown={keydown}
  onclick={onClick}
  ondragenter={dropzone.handleDragEnter}
  ondragleave={dropzone.handleDragLeave}
  ondragover={dropzone.handleDragOver}
  ondrop={dropzone.handleDrop}
  type="button"
>
  {@render children?.()}
</button>
<label class="hidden">
  <input
    {...restProps}
    bind:this={input}
    onchange={dropzone.handleFileInput}
    multiple={dropzone.multiple}
    disabled={dropzone.disabled}
    class="absolute inset-0 block h-full w-full cursor-pointer opacity-0"
    type="file"
  />
</label>
