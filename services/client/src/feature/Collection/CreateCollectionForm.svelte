<script lang="ts">
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';
  import { Label } from '@slink/ui/components/label';

  import { t } from '$lib/i18n';
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
    name: '',
    description: '',
  });

  $effect(() => {
    formData.name = initialData?.name?.decodeHtmlEntities() ?? '';
    formData.description = initialData?.description?.decodeHtmlEntities() ?? '';
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
    {#snippet title()}
      {mode === 'edit'
        ? $t('collection.form.edit_collection')
        : $t('collection.form.create_collection')}
    {/snippet}
    {#snippet description()}
      {mode === 'edit'
        ? $t('collection.form.update_details')
        : $t('collection.form.organize_description')}
    {/snippet}
  </Modal.Header>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label={$t('collection.form.collection_name')}
        bind:value={formData.name}
        placeholder={$t('collection.form.collection_name_placeholder')}
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
        <Label class="mb-3">{$t('collection.form.description_optional')}</Label>
        <textarea
          bind:value={formData.description}
          placeholder={$t('collection.form.description_placeholder')}
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
        {#snippet title()}{$t('collection.form.notice_title')}{/snippet}
        {#snippet message()}
          {$t('collection.form.notice_message')}
        {/snippet}
      </Modal.Notice>
    {/if}

    <Modal.Footer
      {isSubmitting}
      submitText={mode === 'edit'
        ? $t('collection.form.save_changes')
        : $t('collection.form.create_collection')}
      submitDisabled={!formData.name.trim()}
      {onCancel}
    />
  </form>
</div>
