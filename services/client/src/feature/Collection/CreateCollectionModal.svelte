<script lang="ts">
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';
  import { Label } from '@slink/ui/components/label';

  import Icon from '@iconify/svelte';

  interface Props {
    isSubmitting?: boolean;
    errors?: Record<string, string>;
    onSubmit: (data: { name: string; description?: string }) => void;
    onCancel: () => void;
  }

  let {
    isSubmitting = false,
    errors = {},
    onSubmit,
    onCancel,
  }: Props = $props();

  let formData = $state({
    name: '',
    description: '',
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
  <Modal.Header variant="blue">
    {#snippet icon()}
      <Icon icon="lucide:folder-plus" />
    {/snippet}
    {#snippet title()}Create Collection{/snippet}
    {#snippet description()}Organize your images into a shareable collection{/snippet}
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

    <Modal.Footer
      variant="blue"
      {isSubmitting}
      submitText="Create Collection"
      submitDisabled={!formData.name.trim()}
      {onCancel}
    />
  </form>
</div>
