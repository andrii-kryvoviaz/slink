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
    modalAccentVariants,
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
    background = 'frosted',
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
    class={cn(
      modalContentVariants({ size, animation, background }),
      modalAccentVariants({ variant }),
      className,
    )}
    {...restProps}
  >
    {@render children?.()}
    {#if showCloseButton}
      <DialogPrimitive.Close
        class="opacity-0 group-hover:opacity-100 w-8 h-8 flex items-center justify-center rounded-full bg-surface-inverse-foreground/10 dark:bg-surface-inverse-foreground/5 backdrop-blur-sm border border-surface-inverse-foreground/[0.08] dark:border-surface-inverse-foreground/[0.04] hover:border-danger/20 text-foreground-subtle dark:text-muted-foreground hover:text-danger hover:bg-danger-subtle transition-all duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-danger/20 absolute end-4 top-4"
      >
        <XIcon class="h-4 w-4" />
        <span class="sr-only">Close</span>
      </DialogPrimitive.Close>
    {/if}
  </DialogPrimitive.Content>
</Dialog.Portal>
