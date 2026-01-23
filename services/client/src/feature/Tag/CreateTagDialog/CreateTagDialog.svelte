<script lang="ts">
  import { CreateTagForm } from '@slink/feature/Tag';
  import { Dialog } from '@slink/ui/components/dialog';

  import type { CreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { createTagSelectionState } from '@slink/lib/state/TagSelectionState.svelte';

  interface Props {
    modalState: CreateTagModalState;
  }

  let { modalState }: Props = $props();

  const tagState = createTagSelectionState();

  const handleOpenChange = (open: boolean) => {
    if (!open && modalState.isOpen) {
      modalState.close();
    }
    if (open && !tagState.isLoaded) {
      tagState.load();
    }
  };
</script>

<Dialog open={modalState.isOpen} onOpenChange={handleOpenChange} size="md">
  {#snippet children()}
    <CreateTagForm
      tags={tagState.tags}
      isCreating={modalState.isSubmitting}
      errors={modalState.errors}
      onSubmit={(data) => modalState.submit(data)}
      onCancel={() => modalState.close()}
    />
  {/snippet}
</Dialog>
