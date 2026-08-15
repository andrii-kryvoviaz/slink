<script lang="ts">
  import { AlertDialog as AlertDialogPrimitive } from 'bits-ui';

  import {
    type WithoutChild,
    type WithoutChildrenOrChild,
    cn,
  } from '@slink/utils/ui/index.js';

  import AlertDialogOverlay from './alert-dialog-overlay.svelte';

  let {
    ref = $bindable(null),
    class: className,
    portalProps,
    ...restProps
  }: WithoutChild<AlertDialogPrimitive.ContentProps> & {
    portalProps?: WithoutChildrenOrChild<AlertDialogPrimitive.PortalProps>;
  } = $props();
</script>

<AlertDialogPrimitive.Portal {...portalProps}>
  <AlertDialogOverlay />
  <AlertDialogPrimitive.Content
    bind:ref
    data-slot="alert-dialog-content"
    class={cn(
      'bg-gradient-to-br from-muted-soft to-muted/50 dark:to-muted/30 border border-border/50 dark:border-border/30 rounded-2xl shadow-lg backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed left-[50%] top-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 p-6 duration-200 sm:max-w-lg',
      className,
    )}
    {...restProps}
  />
</AlertDialogPrimitive.Portal>
