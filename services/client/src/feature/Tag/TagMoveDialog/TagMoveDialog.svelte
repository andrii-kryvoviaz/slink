<script lang="ts">
  import { useTagOperations } from '@slink/feature/Tag';
  import { Combobox } from '@slink/ui/components/combobox';
  import type { ComboboxItem } from '@slink/ui/components/combobox';
  import { Dialog, Modal } from '@slink/ui/components/dialog';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
  }

  let { tag, open = $bindable(), onOpenChange, onMove }: Props = $props();

  let selectedParentId = $state('');
  let isMoving = $state(false);

  const { loadTags, isLoadingTags, tagsResponse } = useTagOperations();

  function collectDescendantIds(t: Tag): Set<string> {
    const ids = new Set<string>();
    ids.add(t.id);
    if (t.children) {
      for (const child of t.children) {
        for (const id of collectDescendantIds(child)) {
          ids.add(id);
        }
      }
    }
    return ids;
  }

  const excludedIds = $derived(collectDescendantIds(tag));

  const tagItems = $derived<ComboboxItem[]>(
    ($tagsResponse?.data || [])
      .filter((t) => !excludedIds.has(t.id))
      .map((t) => ({
        value: t.id,
        label: t.name,
        description: t.path,
      })),
  );

  const handleTagSearch = (query: string) => {
    loadTags(query);
  };

  const handleOpenChange = (newOpen: boolean) => {
    if (!newOpen) {
      selectedParentId = '';
    }
    open = newOpen;
    onOpenChange?.(newOpen);
  };

  async function handleSubmit(event: Event) {
    event.preventDefault();
    try {
      isMoving = true;
      const newParentId = selectedParentId || null;
      await onMove(tag.id, newParentId);
      handleOpenChange(false);
    } catch {
      console.warn($t('tag.move_dialog.failed_to_move'));
    } finally {
      isMoving = false;
    }
  }

  function handleCancel() {
    handleOpenChange(false);
  }
</script>

<Dialog {open} onOpenChange={handleOpenChange} size="md" variant="blue">
  {#snippet children()}
    <div class="space-y-6">
      <Modal.Header>
        {#snippet icon()}
          <Icon icon="lucide:arrow-right-left" />
        {/snippet}
        {#snippet title()}{$t('tag.move_dialog.title')}{/snippet}
        {#snippet description()}
          {$t('tag.move_dialog.description_prefix')} "{tag.name}"
          {$t('tag.move_dialog.description_suffix')}
        {/snippet}
      </Modal.Header>

      <form onsubmit={handleSubmit} class="space-y-6">
        <div class="space-y-5">
          <div class="space-y-3">
            <label
              for="new-parent-tag"
              class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3 block"
            >
              {$t('tag.move_dialog.new_parent_tag')}
            </label>
            <Combobox
              items={tagItems}
              bind:value={selectedParentId}
              placeholder={$t('tag.move_dialog.search_parent_placeholder')}
              onSearch={handleTagSearch}
              loading={$isLoadingTags}
              clearable={true}
              emptyMessage={$t('tag.move_dialog.empty_message')}
            />
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {$t('tag.move_dialog.leave_empty_hint')}
            </p>
          </div>
        </div>

        <Modal.Footer
          isSubmitting={isMoving}
          submitText={isMoving
            ? $t('tag.move_dialog.moving')
            : $t('tag.move_dialog.submit')}
          onCancel={handleCancel}
        />
      </form>
    </div>
  {/snippet}
</Dialog>
