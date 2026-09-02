<script lang="ts">
  import {
    Toaster as Sonner,
    type ToasterProps as SonnerProps,
  } from 'svelte-sonner';

  import { page } from '$app/state';

  let { ...restProps }: SonnerProps = $props();
</script>

<Sonner
  theme={page.data.settings.mode.current}
  class="toaster group"
  style="--normal-bg: var(--color-popover); --normal-text: var(--color-on-popover); --normal-border: var(--color-border); --width: auto;"
  toastOptions={{
    unstyled: true,
    class: 'w-full sm:min-w-[380px] sm:w-auto',
  }}
  {...restProps}
/>
