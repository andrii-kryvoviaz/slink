<script lang="ts">
  import XIcon from '@lucide/svelte/icons/x';
  import { Dialog as DialogPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { type WithoutChildrenOrChild, cn } from '@slink/utils/ui/index.js';

  import * as Dialog from './index.js';
  import { setModalContext } from './modal-context.js';
  import {
    type ModalAnimation,
    type ModalBackdrop,
    type ModalBackground,
    type ModalSize,
    type ModalVariant,
    modalContentVariants,
  } from './modal.theme.js';

  let {
    ref = $bindable(null),
    class: className,
    portalProps,
    children,
    showCloseButton = true,
    variant = 'blue',
    backdrop = 'enabled',
    animation = 'fade',
    size = 'md',
    background = 'glass',
    ...restProps
  }: WithoutChildrenOrChild<DialogPrimitive.ContentProps> & {
    portalProps?: DialogPrimitive.PortalProps;
    children: Snippet;
    showCloseButton?: boolean;
    variant?: ModalVariant;
    backdrop?: ModalBackdrop;
    animation?: ModalAnimation;
    size?: ModalSize;
    background?: ModalBackground;
  } = $props();

  $effect(() => {
    setModalContext({ variant, backdrop, animation });
  });
</script>

<Dialog.Portal {...portalProps}>
  <Dialog.Overlay {backdrop} {animation} />
  <DialogPrimitive.Content
    bind:ref
    data-slot="dialog-content"
    class={cn(modalContentVariants({ size, animation, background }), className)}
    {...restProps}
  >
    {@render children?.()}
    {#if showCloseButton}
      <DialogPrimitive.Close
        class="opacity-0 group-hover:opacity-100 w-8 h-8 flex items-center justify-center rounded-lg bg-white/80 dark:bg-slate-700/80 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 shadow-sm absolute end-4 top-4"
      >
        <XIcon class="h-4 w-4" />
        <span class="sr-only">Close</span>
      </DialogPrimitive.Close>
    {/if}
  </DialogPrimitive.Content>
</Dialog.Portal>
