<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import * as Dialog from './index.js';

  type Props = {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    children?: Snippet;
    class?: string;
  };

  let {
    open = $bindable(false),
    onOpenChange,
    title,
    description,
    size = 'md',
    children,
    class: className,
  }: Props = $props();

  const sizeClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-lg',
    lg: 'sm:max-w-2xl',
    xl: 'sm:max-w-4xl',
  };

  function handleOpenChange(newOpen: boolean) {
    open = newOpen;
    onOpenChange?.(newOpen);
  }
</script>

<Dialog.Root bind:open onOpenChange={handleOpenChange}>
  <Dialog.Content class={cn(sizeClasses[size], className)}>
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
