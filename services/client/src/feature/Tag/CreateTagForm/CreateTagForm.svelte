<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tags: Tag[];
    isCreating: boolean;
    onSubmit: (data: { name: string; parentId?: string }) => void;
    onCancel: () => void;
    errors?: Record<string, string>;
  }

  let { tags, isCreating, onSubmit, onCancel, errors = {} }: Props = $props();

  let formData = $state({
    name: '',
    parentId: '',
  });

  const selectedParentTag = $derived(() => {
    if (!formData.parentId) return [];
    const tag = tags.find((t) => t.id === formData.parentId);
    return tag ? [tag] : [];
  });

  const handleParentTagChange = (selectedTags: Tag[]) => {
    formData.parentId = selectedTags.length > 0 ? selectedTags[0].id : '';
  };

  function handleSubmit(event: Event) {
    event.preventDefault();
    const data = {
      name: formData.name.trim(),
      parentId: formData.parentId || undefined,
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
  <Modal.Header variant="green">
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
        bind:value={formData.name}
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
        <TagSelector
          selectedTags={selectedParentTag()}
          onTagsChange={handleParentTagChange}
          placeholder="Search for parent tag..."
          variant="minimal"
          allowCreate={false}
        />
      </div>
    </div>

    <Modal.Notice variant="success">
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
      variant="green"
      isSubmitting={isCreating}
      submitText={isCreating ? 'Creating...' : 'Create Tag'}
      submitDisabled={!formData.name.trim()}
      {onCancel}
    />
  </form>
</div>
