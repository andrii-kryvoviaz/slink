<script lang="ts">
  import { RemoveFromCollectionPopover } from '@slink/feature/Collection';
  import {
    ActionsMenu,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    onRemove: () => void;
  }

  let { loading = false, onRemove }: Props = $props();

  let confirmOpen = $state(false);

  const handleRemoveClick = () => {
    confirmOpen = true;
  };

  const handleConfirm = () => {
    onRemove();
    confirmOpen = false;
  };

  const handleCancel = () => {
    confirmOpen = false;
  };
</script>

<div class="relative -mr-1.5">
  <ActionsMenu tone="ghost" label="Collection item actions">
    <DropdownSimpleGroup>
      {#if !confirmOpen}
        <DropdownSimpleItem
          danger={true}
          on={{ click: handleRemoveClick }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="ph:trash" class="h-4 w-4" />
          {/snippet}
          <span>Remove from Collection</span>
        </DropdownSimpleItem>
      {:else}
        <RemoveFromCollectionPopover
          {loading}
          close={handleCancel}
          confirm={handleConfirm}
        />
      {/if}
    </DropdownSimpleGroup>
  </ActionsMenu>
</div>
