<script lang="ts">
  import { Checkbox } from '@slink/ui/components/checkbox';

  import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

  interface Props {
    itemId: string;
    selectionState?: ImageSelectionState;
  }

  let { itemId, selectionState }: Props = $props();

  const checked = $derived(selectionState?.isSelected(itemId) ?? false);

  const handleChange = () => {
    selectionState?.toggle(itemId);
  };
</script>

<Checkbox {checked} onCheckedChange={handleChange} />
