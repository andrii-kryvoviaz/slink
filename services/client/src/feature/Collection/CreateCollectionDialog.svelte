<script lang="ts">
  import { CreateCollectionForm } from '@slink/feature/Collection';
  import { Dialog } from '@slink/ui/components/dialog';

  import type { CreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  interface Props {
    modalState: CreateCollectionModalState;
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
    <CreateCollectionForm
      isSubmitting={modalState.isSubmitting}
      errors={modalState.errors}
      onSubmit={(data) => modalState.submit(data)}
      onCancel={() => modalState.close()}
    />
  {/snippet}
</Dialog>
