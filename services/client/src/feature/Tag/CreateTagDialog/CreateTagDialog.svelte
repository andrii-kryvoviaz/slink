<script lang="ts">
  import { CreateTagForm } from '@slink/feature/Tag';
  import { Dialog } from '@slink/ui/components/dialog';

  import type { CreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';

  interface Props {
    modalState: CreateTagModalState;
  }

  let { modalState }: Props = $props();

  const handleOpenChange = (open: boolean) => {
    if (!open && modalState.isOpen) {
      modalState.close();
    }
  };
</script>

<Dialog
  open={modalState.isOpen}
  onOpenChange={handleOpenChange}
  size="md"
  variant="blue"
>
  {#snippet children()}
    <CreateTagForm
      isCreating={modalState.isSubmitting}
      errors={modalState.errors}
      onSubmit={(data) => modalState.submit(data)}
      onCancel={() => modalState.close()}
    />
  {/snippet}
</Dialog>
