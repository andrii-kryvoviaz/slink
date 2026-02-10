<script lang="ts">
  import { CollectionListView } from '@slink/feature/Collection';
  import { CreateCollectionDialog } from '@slink/feature/Collection';
  import type { CollectionPickerVariant } from '@slink/feature/Collection/CollectionPicker/CollectionPicker.theme';

  import type { CollectionResponse } from '@slink/api/Response';

  import type { CollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
  import type { CreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  interface Props {
    pickerState: CollectionPickerState;
    createModalState: CreateCollectionModalState;
    variant?: CollectionPickerVariant;
    onToggle?: (result: { added: boolean; collectionId: string }) => void;
    onBeforeCreate?: () => void;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    onToggle,
    onBeforeCreate,
  }: Props = $props();

  const selectedIds = $derived(
    pickerState.collections
      .filter((c) => pickerState.isInCollection(c.id))
      .map((c) => c.id),
  );

  const handleToggle = async (collection: CollectionResponse) => {
    const result = await pickerState.toggle(collection);
    if (result && onToggle) {
      onToggle(result);
    }
  };

  const handleCreateNew = () => {
    onBeforeCreate?.();
    createModalState.open((collection) => {
      pickerState.addCollection(collection);
    });
  };
</script>

<CollectionListView
  collections={pickerState.collections}
  {selectedIds}
  isLoading={pickerState.isLoading}
  togglingId={pickerState.actionLoadingId}
  {variant}
  onToggle={handleToggle}
  onCreateNew={handleCreateNew}
/>

<CreateCollectionDialog modalState={createModalState} />
