<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  import { controls } from './Popover.theme';

  interface Props {
    onBack: () => void;
    onConfirm: () => void | Promise<void>;
  }

  let { onBack, onConfirm }: Props = $props();

  let isSubmitting: boolean = $state(false);

  const handleConfirm = async (): Promise<void> => {
    if (isSubmitting) {
      return;
    }

    isSubmitting = true;

    try {
      await onConfirm();
    } finally {
      isSubmitting = false;
    }
  };

  const detail = controls.detail();
</script>

<div in:fly|local={{ x: 6, duration: 120 }} class={detail.root()}>
  <div class={detail.header()}>
    <button
      type="button"
      class={detail.back()}
      onclick={onBack}
      aria-label="Back to options"
      disabled={isSubmitting}
    >
      <Icon icon="ph:caret-left" class={detail.backIcon()} />
    </button>

    <div class={detail.labels()}>
      <span class={detail.title()}>Unpublish</span>
      <span class={detail.description()}>
        The link will stop working for anyone who has it.
      </span>
    </div>
  </div>

  <div class="flex gap-2">
    <Button
      variant="outline"
      size="sm"
      rounded="md"
      onclick={onBack}
      disabled={isSubmitting}
      class="flex-1"
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      size="sm"
      rounded="md"
      onclick={handleConfirm}
      loading={isSubmitting}
      class="flex-1"
    >
      {#snippet leftIcon()}
        <Icon icon="ph:eye-slash" class="h-4 w-4" />
      {/snippet}
      Unpublish
    </Button>
  </div>
</div>
