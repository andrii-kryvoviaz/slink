<script lang="ts">
  import XIcon from '@lucide/svelte/icons/x';
  import { Dialog as DialogPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { type WithoutChildrenOrChild, cn } from '@slink/utils/ui/index.js';

  import * as Dialog from './index.js';

  let {
    ref = $bindable(null),
    class: className,
    portalProps,
    children,
    showCloseButton = true,
    ...restProps
  }: WithoutChildrenOrChild<DialogPrimitive.ContentProps> & {
    portalProps?: DialogPrimitive.PortalProps;
    children: Snippet;
    showCloseButton?: boolean;
  } = $props();
</script>

<Dialog.Portal {...portalProps}>
  <Dialog.Overlay />
  <DialogPrimitive.Content
    bind:ref
    data-slot="dialog-content"
    class={cn(
      'group bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-700/30 border border-slate-200/50 dark:border-slate-700/30 rounded-2xl shadow-lg backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 fixed left-[50%] top-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 p-6 duration-200 sm:max-w-lg',
      className,
    )}
    {...restProps}
  >
    {@render children?.()}
    {#if showCloseButton}
      <DialogPrimitive.Close
        class="opacity-0 group-hover:opacity-100 w-8 h-8 flex items-center justify-center rounded-lg bg-white/80 dark:bg-gray-700/80 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 shadow-sm absolute end-4 top-4"
      >
        <XIcon class="h-4 w-4" />
        <span class="sr-only">Close</span>
      </DialogPrimitive.Close>
    {/if}
  </DialogPrimitive.Content>
</Dialog.Portal>
