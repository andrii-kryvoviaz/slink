<script lang="ts">
  import * as Dialog from '@slink/ui/components/dialog';
  import type { Snippet } from 'svelte';

  import {
    type ModalVariant,
    modalIcon,
    modalIconContainer,
  } from './modal-content.theme.js';

  type Props = {
    variant?: ModalVariant;
    icon: Snippet;
    title: Snippet;
    description?: Snippet;
  };

  let { variant = 'blue', icon, title, description }: Props = $props();
</script>

<Dialog.Header class="flex flex-row items-start gap-4 pb-2">
  <div class={modalIconContainer({ variant })}>
    <span class={modalIcon({ variant })}>
      {@render icon()}
    </span>
  </div>
  <div class="flex-1 min-w-0">
    <Dialog.Title
      class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
    >
      {@render title()}
    </Dialog.Title>
    {#if description}
      <Dialog.Description
        class="text-sm text-slate-600 dark:text-slate-400 mt-1"
      >
        {@render description()}
      </Dialog.Description>
    {/if}
  </div>
</Dialog.Header>
