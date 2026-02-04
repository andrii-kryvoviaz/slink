<script lang="ts">
  import { mode } from 'mode-watcher';
  import {
    Toaster as Sonner,
    type ToasterProps as SonnerProps,
  } from 'svelte-sonner';

  let { ...restProps }: SonnerProps = $props();
</script>

<Sonner
  theme={mode.current}
  class="toaster group"
  style="--normal-bg: var(--color-popover); --normal-text: var(--color-popover-foreground); --normal-border: var(--color-border); --width: auto;"
  toastOptions={{
    unstyled: true,
    class: 'w-full sm:min-w-[380px] sm:w-auto',
  }}
  {...restProps}
/>
