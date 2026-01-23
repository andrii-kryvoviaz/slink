<script lang="ts">
  import { RemoveFromCollectionPopover } from '@slink/feature/Collection';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';

  import Icon from '@iconify/svelte';
  import { readable } from 'svelte/store';

  interface Props {
    loading?: boolean;
    onRemove: () => void;
  }

  let { loading = false, onRemove }: Props = $props();

  let confirmOpen = $state(false);

  const loadingStore = $derived(readable(loading));

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
  <DropdownSimple variant="invisible" size="xs">
    {#snippet trigger()}
      <button
        class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-150"
      >
        <Icon icon="heroicons:ellipsis-vertical" class="w-4 h-4" />
      </button>
    {/snippet}

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
          loading={loadingStore}
          close={handleCancel}
          confirm={handleConfirm}
        />
      {/if}
    </DropdownSimpleGroup>
  </DropdownSimple>
</div>
