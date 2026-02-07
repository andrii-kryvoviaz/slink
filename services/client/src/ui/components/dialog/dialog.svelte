<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import * as Dialog from './index.js';
  import type {
    ModalAnimation,
    ModalBackdrop,
    ModalSize,
    ModalVariant,
  } from './modal.theme.js';

  type Props = {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    title?: string;
    description?: string;
    size?: ModalSize;
    variant?: ModalVariant;
    backdrop?: ModalBackdrop;
    animation?: ModalAnimation;
    children?: Snippet;
    class?: string;
  };

  let {
    open = $bindable(false),
    onOpenChange,
    title,
    description,
    size = 'md',
    variant = 'blue',
    backdrop = 'enabled',
    animation = 'fade',
    children,
    class: className,
  }: Props = $props();

  function handleOpenChange(newOpen: boolean) {
    open = newOpen;
    onOpenChange?.(newOpen);
  }
</script>

<Dialog.Root bind:open onOpenChange={handleOpenChange}>
  <Dialog.Content
    {size}
    {variant}
    {backdrop}
    {animation}
    onpaste={(e: ClipboardEvent) => e.stopPropagation()}
    class={cn(className)}
  >
    {#if title || description}
      <Dialog.Header>
        {#if title}
          <Dialog.Title>{title}</Dialog.Title>
        {/if}
        {#if description}
          <Dialog.Description>{description}</Dialog.Description>
        {/if}
      </Dialog.Header>
    {/if}

    {#if children}
      {@render children()}
    {/if}
  </Dialog.Content>
</Dialog.Root>
