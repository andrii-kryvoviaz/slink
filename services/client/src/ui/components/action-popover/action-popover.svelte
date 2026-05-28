<script lang="ts">
  import { Popover as PopoverPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  interface Props {
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    children?: Snippet;
  }

  let { open = $bindable(false), onOpenChange, children }: Props = $props();

  function handleOpenChange(next: boolean): void {
    open = next;
    onOpenChange?.(next);
  }
</script>

<PopoverPrimitive.Root bind:open onOpenChange={handleOpenChange}>
  {@render children?.()}
</PopoverPrimitive.Root>
