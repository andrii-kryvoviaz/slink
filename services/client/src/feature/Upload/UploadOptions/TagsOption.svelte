<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
  }

  let { selectedTags = [], onTagsChange, disabled = false }: Props = $props();
  let isOpen = $state(false);
  let tagSelectorRef = $state<{ focusInput: () => void }>();

  const shouldShowSelector = $derived(isOpen || selectedTags.length > 0);

  const handleOpenSelector = () => {
    if (!disabled) {
      isOpen = true;
      setTimeout(() => tagSelectorRef?.focusInput(), 0);
    }
  };

  $effect(() => {
    if (selectedTags.length === 0) {
      isOpen = false;
    }
  });
</script>

{#if !shouldShowSelector}
  <Button
    type="button"
    onclick={handleOpenSelector}
    {disabled}
    variant="ghost"
    size="md"
    rounded="lg"
  >
    <Icon icon="ph:plus" class="h-4 w-4" />
    <span>Add tags</span>
  </Button>
{:else}
  <TagSelector
    bind:this={tagSelectorRef}
    {selectedTags}
    {onTagsChange}
    {disabled}
    placeholder="Search or add tags..."
    variant="neon"
    allowCreate={true}
    hideIcon={false}
  />
{/if}
