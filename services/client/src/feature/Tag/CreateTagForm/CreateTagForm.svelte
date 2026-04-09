<script lang="ts">
  import { useTagOperations } from '@slink/feature/Tag';
  import { Combobox } from '@slink/ui/components/combobox';
  import type { ComboboxItem } from '@slink/ui/components/combobox';
  import { Modal } from '@slink/ui/components/dialog';
  import { Input } from '@slink/ui/components/input';

  import { t } from '$lib/i18n';
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
    {#snippet title()}{$t('pages.tags.create_tag')}{/snippet}
    {#snippet description()}{$t('tag.create_form.description')}{/snippet}
  </Modal.Header>

  <form onsubmit={handleSubmit} class="space-y-6">
    <div class="space-y-5">
      <Input
        label={$t('tag.create_form.tag_name')}
        bind:value={name}
        placeholder={$t('tag.create_form.tag_name_placeholder')}
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
          {$t('tag.create_form.parent_tag_optional')}
        </label>
        <Combobox
          items={tagItems}
          bind:value={selectedParentTagId}
          placeholder={$t('tag.create_form.search_parent_placeholder')}
          onSearch={handleTagSearch}
          loading={$isLoadingTags}
          clearable={true}
          emptyMessage={$t('tag.create_form.empty_message')}
        />
      </div>
    </div>

    <Modal.Notice variant="info">
      {#snippet icon()}
        <Icon icon="lucide:info" />
      {/snippet}
      {#snippet title()}{$t('tag.create_form.notice_title')}{/snippet}
      {#snippet message()}
        {$t('tag.create_form.notice_message')}
      {/snippet}
    </Modal.Notice>

    <Modal.Footer
      isSubmitting={isCreating}
      submitText={isCreating
        ? $t('tag.create_form.creating')
        : $t('pages.tags.create_tag')}
      submitDisabled={!name.trim()}
      {onCancel}
    />
  </form>
</div>
