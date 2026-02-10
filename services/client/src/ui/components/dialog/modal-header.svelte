<script lang="ts">
  import * as Dialog from '@slink/ui/components/dialog';
  import type { Snippet } from 'svelte';

  import { getModalVariant } from './modal-context.js';
  import {
    type ModalVariant,
    modalHeaderIconContainerVariants,
    modalHeaderIconVariants,
  } from './modal.theme.js';

  type Props = {
    variant?: ModalVariant;
    icon: Snippet;
    title: Snippet;
    description?: Snippet;
  };

  let { variant: variantProp, icon, title, description }: Props = $props();

  const variant = $derived(variantProp ?? getModalVariant());
</script>

<Dialog.Header
  class="flex flex-row items-start gap-4 pb-4 border-b border-foreground/[0.04] dark:border-foreground/[0.06]"
>
  <div class={modalHeaderIconContainerVariants({ variant })}>
    <span class={modalHeaderIconVariants({ variant })}>
      {@render icon()}
    </span>
  </div>
  <div class="flex-1 min-w-0">
    <Dialog.Title
      class="text-lg font-semibold text-slate-900 dark:text-white/95 tracking-tight"
    >
      {@render title()}
    </Dialog.Title>
    {#if description}
      <Dialog.Description
        class="text-sm text-slate-600 dark:text-slate-400/80 mt-1"
      >
        {@render description()}
      </Dialog.Description>
    {/if}
  </div>
</Dialog.Header>
