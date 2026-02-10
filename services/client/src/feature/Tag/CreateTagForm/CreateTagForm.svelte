<script lang="ts">
  import { useTagOperations } from '@slink/feature/Tag';
  import { Combobox } from '@slink/ui/components/combobox';
  import type { ComboboxItem } from '@slink/ui/components/combobox';
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  interface Props {
    isCreating: boolean;
    onSubmit: (data: { name: string; parentId?: string }) => void;
    onCancel: () => void;
    errors?: Record<string, string>;
  }

  let { isCreating, onSubmit, onCancel, errors = {} }: Props = $props();

  let name = $state('');
  let selectedParentTagId = $state('');

  const { loadTags, isLoadingTags, tagsResponse } = useTagOperations();

  const tagItems = $derived<ComboboxItem[]>(
    ($tagsResponse?.data || []).map((tag) => ({
      value: tag.id,
      label: tag.name,
      description: tag.path,
    })),
  );

  const handleTagSearch = (query: string) => {
    loadTags(query);
  };

  function handleSubmit(event: Event) {
    event.preventDefault();
    const data = {
      name: name.trim(),
      parentId: selectedParentTagId || undefined,
    };
    onSubmit(data);
  }

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !isCreating) {
      event.preventDefault();
      handleSubmit(event);
    }
  }
</script>

<div class="space-y-6">
  <Modal.Header>
    {#snippet icon()}
      <Icon icon="lucide:tag" />
    {/snippet}
    {#snippet title()}Create Tag{/snippet}
    {#snippet description()}Add a new tag to organize your images{/snippet}
  </Modal.Header>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label="Tag Name"
        bind:value={name}
        placeholder="e.g., Nature, Photography, Art"
        error={errors?.name}
        size="md"
        rounded="lg"
        required
        onkeydown={handleKeydown}
      >
        {#snippet leftIcon()}
          <Icon icon="lucide:hash" class="h-4 w-4 text-slate-400" />
        {/snippet}
      </Input>

      <div class="space-y-3">
        <label
          for="parent-tag"
          class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3 block"
        >
          Parent Tag (Optional)
        </label>
        <Combobox
          items={tagItems}
          bind:value={selectedParentTagId}
          placeholder="Search for parent tag..."
          onSearch={handleTagSearch}
          loading={$isLoadingTags}
          clearable={true}
          emptyMessage="No matching tags found."
        />
      </div>
    </div>

    <Modal.Notice variant="info">
      {#snippet icon()}
        <Icon icon="lucide:info" />
      {/snippet}
      {#snippet title()}Tag Organization{/snippet}
      {#snippet message()}
        Tags help organize your images. Parent tags create a hierarchy for
        better organization. You can always move tags later or create sub-tags
        within existing ones.
      {/snippet}
    </Modal.Notice>

    <Modal.Footer
      isSubmitting={isCreating}
      submitText={isCreating ? 'Creating...' : 'Create Tag'}
      submitDisabled={!name.trim()}
      {onCancel}
    />
  </form>
</div>
