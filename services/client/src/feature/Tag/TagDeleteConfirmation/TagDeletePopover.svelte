<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { pluralize } from '@slink/lib/utils/string/pluralize';

  interface Props {
    tag: Tag;
    loading: boolean;
    onConfirm: () => void;
    onCancel: () => void;
  }

  let { tag, loading, onConfirm, onCancel }: Props = $props();

  const hasImages = $derived(tag.imageCount > 0);
  const hasChildren = $derived(tag.children && tag.children.length > 0);
  const hasCascade = $derived(hasImages || hasChildren);
  const childrenCount = $derived(tag.children?.length || 0);
  const childrenText = $derived(
    `Delete ${pluralize(childrenCount, 'child tag')}`,
  );
  const imagesText = $derived(
    `Remove tag from ${pluralize(tag.imageCount, 'image')}`,
  );
</script>

<div class="w-full max-w-sm p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm shrink-0"
    >
      <Icon
        icon="heroicons:trash"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Delete Tag
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  {#if hasCascade}
    <div
      class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-3"
    >
      <h4 class="text-xs font-medium text-amber-800 dark:text-amber-200 mb-1">
        This will cascade:
      </h4>
      <ul class="text-xs text-amber-700 dark:text-amber-300 space-y-0.5">
        {#if hasChildren}
          <li>&bull; {childrenText}</li>
        {/if}
        {#if hasImages}
          <li>&bull; {imagesText}</li>
        {/if}
      </ul>
    </div>
  {/if}

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={onConfirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={loading}
    >
      {#if loading}
        <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
      {:else}
        <Icon icon="heroicons:trash" class="h-4 w-4 mr-2" />
      {/if}
      Delete
    </Button>
  </div>
</div>
