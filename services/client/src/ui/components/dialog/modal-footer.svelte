<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import * as Dialog from '@slink/ui/components/dialog';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { getModalVariant } from './modal-context.js';
  import { type ModalVariant, buttonVariantMap } from './modal.theme.js';

  type Props = {
    variant?: ModalVariant;
    isSubmitting?: boolean;
    submitText?: string;
    cancelText?: string;
    submitDisabled?: boolean;
    onCancel?: () => void;
    onSubmit?: () => void;
    actions?: Snippet;
  };

  let {
    variant: variantProp,
    isSubmitting = false,
    submitText = 'Submit',
    cancelText = 'Cancel',
    submitDisabled = false,
    onCancel,
    onSubmit,
    actions,
  }: Props = $props();

  const variant = $derived(variantProp ?? getModalVariant());
  const submitVariant = $derived(buttonVariantMap[variant] as any);
</script>

<Dialog.Footer class="flex gap-3 pt-2">
  {#if actions}
    {@render actions()}
  {:else}
    <Button
      type="button"
      variant="glass"
      size="sm"
      rounded="full"
      class="flex-1"
      onclick={onCancel}
      disabled={isSubmitting}
    >
      {cancelText}
    </Button>
    <Button
      type={onSubmit ? 'button' : 'submit'}
      variant={submitVariant}
      size="sm"
      rounded="full"
      class="flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200"
      disabled={submitDisabled || isSubmitting}
      onclick={onSubmit}
    >
      {#if isSubmitting}
        <Icon icon="svg-spinners:90-ring-with-bg" class="h-4 w-4 mr-2" />
      {/if}
      {submitText}
    </Button>
  {/if}
</Dialog.Footer>
