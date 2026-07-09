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
    if (ev.target !== ev.currentTarget) return;
    if (![' ', 'Enter'].includes(ev.key)) return;

    ev.preventDefault();
    input?.click();
  }

  function onClick() {
    input?.click();
  }
</script>

<div
  data-slot="dropzone-input"
  class={cn(dropzoneInputTheme(), className)}
  role="button"
  tabindex={dropzone.disabled ? -1 : 0}
  aria-disabled={dropzone.disabled}
  onkeydown={keydown}
  onclick={onClick}
>
  {@render children?.()}
</div>
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
