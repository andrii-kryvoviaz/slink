<script lang="ts">
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';
  import { Label } from '@slink/ui/components/label';

  import Icon from '@iconify/svelte';

  interface Props {
    mode?: 'create' | 'edit';
    initialData?: { name: string; description?: string };
    isSubmitting?: boolean;
    errors?: Record<string, string>;
    onSubmit: (data: { name: string; description?: string }) => void;
    onCancel: () => void;
  }

  let {
    mode = 'create',
    initialData,
    isSubmitting = false,
    errors = {},
    onSubmit,
    onCancel,
  }: Props = $props();

  let formData = $state({
    name: initialData?.name?.decodeHtmlEntities() ?? '',
    description: initialData?.description?.decodeHtmlEntities() ?? '',
  });

  $effect(() => {
    if (initialData) {
      formData.name = initialData.name.decodeHtmlEntities();
      formData.description =
        initialData.description?.decodeHtmlEntities() ?? '';
    }
  });

  function handleSubmit(event: Event) {
    event.preventDefault();
    onSubmit({
      name: formData.name.trim(),
      description: formData.description.trim() || undefined,
    });
  }

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey && !isSubmitting) {
      event.preventDefault();
      handleSubmit(event);
    }
  }
</script>

<div class="space-y-6">
  <Modal.Header>
    {#snippet icon()}
      <Icon icon={mode === 'edit' ? 'lucide:pencil' : 'lucide:folder-plus'} />
    {/snippet}
    {#snippet title()}{mode === 'edit' ? 'Edit' : 'Create'} Collection{/snippet}
    {#snippet description()}
      {mode === 'edit'
        ? 'Update your collection details'
        : 'Organize your images into a shareable collection'}
    {/snippet}
  </Modal.Header>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label="Collection Name"
        bind:value={formData.name}
        placeholder="e.g., Summer Vacation, Portfolio"
        error={errors?.name}
        maxlength={100}
        size="md"
        rounded="lg"
        required
        onkeydown={handleKeydown}
      >
        {#snippet leftIcon()}
          <Icon icon="lucide:folder" class="h-4 w-4 text-slate-400" />
        {/snippet}
      </Input>

      <div>
        <Label class="mb-3">Description (Optional)</Label>
        <textarea
          bind:value={formData.description}
          placeholder="What's this collection about?"
          maxlength={500}
          rows={3}
          class="border-border bg-background dark:bg-input/30 placeholder:text-muted-foreground shadow-xs flex w-full min-w-0 rounded-lg border px-4 py-2.5 text-base outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] resize-none md:text-sm"
          aria-invalid={errors?.description ? true : undefined}
        ></textarea>
        {#if errors?.description}
          <p class="mt-1.5 text-xs text-input-error">{errors.description}</p>
        {/if}
      </div>
    </div>

    {#if mode === 'create'}
      <Modal.Notice variant="info">
        {#snippet icon()}
          <Icon icon="lucide:info" />
        {/snippet}
        {#snippet title()}Collection Sharing{/snippet}
        {#snippet message()}
          Collections let you group images together and share them with a single
          link. You can add or remove images at any time after creating the
          collection.
        {/snippet}
      </Modal.Notice>
    {/if}

    <Modal.Footer
      {isSubmitting}
      submitText={mode === 'edit' ? 'Save Changes' : 'Create Collection'}
      submitDisabled={!formData.name.trim()}
      {onCancel}
    />
  </form>
</div>
