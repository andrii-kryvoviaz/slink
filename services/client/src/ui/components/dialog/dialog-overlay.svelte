<script lang="ts">
  import { Dialog as DialogPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { getModalAnimation, getModalBackdrop } from './modal-context.js';
  import {
    type ModalAnimation,
    type ModalBackdrop,
    modalOverlayVariants,
  } from './modal.theme.js';

  let {
    ref = $bindable(null),
    class: className,
    backdrop: backdropProp,
    animation: animationProp,
    ...restProps
  }: DialogPrimitive.OverlayProps & {
    backdrop?: ModalBackdrop;
    animation?: ModalAnimation;
  } = $props();

  const backdrop = $derived(backdropProp ?? getModalBackdrop());
  const animation = $derived(animationProp ?? getModalAnimation());
</script>

<DialogPrimitive.Overlay
  bind:ref
  data-slot="dialog-overlay"
  class={cn(modalOverlayVariants({ backdrop, animation }), className)}
  {...restProps}
/>
