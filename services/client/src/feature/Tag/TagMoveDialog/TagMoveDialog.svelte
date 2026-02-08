<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { Dialog } from '@slink/ui/components/dialog';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagParentPath } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
    onMove: (tag: Tag, parentId?: string | null) => Promise<void>;
  }

  let { tag, onMove }: Props = $props();

  let open = $state(false);
  let selectedParent = $state<Tag | null>(null);
  let isSaving = $state(false);

  const currentParentLabel = $derived(
    getTagParentPath(tag) || 'Root',
  );

  const isMoveDisabled = $derived(
    isSaving || (!selectedParent && tag.isRoot),
  );

  const handleMove = async () => {
    if (isMoveDisabled) return;

    try {
      isSaving = true;
      await onMove(tag, selectedParent?.id ?? null);
      open = false;
    } finally {
      isSaving = false;
    }
  };

  const handleMoveToRoot = () => {
    selectedParent = null;
  };

  $effect(() => {
    if (!open) {
      selectedParent = null;
      isSaving = false;
    }
  });
</script>

<button
  type="button"
  class="group relative flex items-center justify-center h-8 w-8 rounded-md bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 hover:border-blue-200 dark:hover:border-blue-800/50 active:scale-95 focus-visible:ring-blue-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transition-all duration-200 ease-out"
  aria-label="Move tag"
  onclick={() => (open = true)}
>
  <Icon icon="ph:folder" class="h-4 w-4" />
  <span class="sr-only">Move tag "{tag.name}"</span>
</button>

<Dialog bind:open size="md" variant="blue">
  {#snippet children()}
    <div class="space-y-4">
      <div class="flex items-start gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/30 border border-blue-200/40 dark:border-blue-800/30"
        >
          <Icon icon="ph:folder" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            Move Tag
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Current parent: {currentParentLabel}
          </p>
        </div>
      </div>

      <TagSelector
        selectedTags={selectedParent ? [selectedParent] : []}
        onTagsChange={(tags) => (selectedParent = tags[0] ?? null)}
        placeholder="Search and select parent tag"
        variant="neon"
        allowCreate={false}
        singleSelect={true}
        excludeTagIds={[tag.id]}
        excludePathPrefix={tag.path}
      />

      <div class="rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800/60 p-3 text-xs text-slate-600 dark:text-slate-300">
        Moving this tag will also move its child tags under the new parent.
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
        <Button
          variant="glass"
          rounded="full"
          size="sm"
          onclick={() => (open = false)}
          class="flex-1"
          disabled={isSaving}
        >
          Cancel
        </Button>
        <Button
          variant="outline"
          rounded="full"
          size="sm"
          onclick={handleMoveToRoot}
          class="flex-1"
          disabled={isSaving || tag.isRoot}
        >
          Move to Root
        </Button>
        <Button
          variant="primary"
          rounded="full"
          size="sm"
          onclick={handleMove}
          class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
          disabled={isMoveDisabled}
        >
          {#if isSaving}
            <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
          {:else}
            <Icon icon="ph:arrow-right" class="h-4 w-4 mr-2" />
          {/if}
          Move Tag
        </Button>
      </div>
    </div>
  {/snippet}
</Dialog>
