<script lang="ts">
  import {
    Toaster as Sonner,
    type ToasterProps as SonnerProps,
  } from 'svelte-sonner';

  import { customization } from '@slink/lib/settings';

  let { ...restProps }: SonnerProps = $props();
</script>

<Sonner
  theme={customization.mode}
  class="toaster group"
  style="--normal-bg: var(--color-popover); --normal-text: var(--color-on-popover); --normal-border: var(--color-border); --width: auto;"
  toastOptions={{
    unstyled: true,
    class: 'w-full sm:min-w-[380px] sm:w-auto',
  }}
  {...restProps}
/>
