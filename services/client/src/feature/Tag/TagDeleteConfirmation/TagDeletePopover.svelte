<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import * as Popover from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
  }

  let { tag, onDelete }: Props = $props();

  let open = $state(false);
  let isDeleting = $state(false);

  const hasImages = $derived(tag.imageCount > 0);
  const hasChildren = $derived(tag.children && tag.children.length > 0);
  const childrenCount = $derived(tag.children?.length || 0);

  const title = $derived('Delete Tag');

  const description = $derived.by(() => {
    if (hasImages && hasChildren) {
      return `This will remove the tag from ${tag.imageCount} images and delete ${childrenCount} child tags.`;
    }

    if (hasImages) {
      return `This will remove the tag from ${tag.imageCount} images.`;
    }

    if (hasChildren) {
      return `This will delete ${childrenCount} child tags.`;
    }

    return 'This action cannot be undone.';
  });

  const confirmText = $derived('Delete Tag');
  const variant = $derived('danger');
  const iconName = $derived('heroicons:trash');
  const iconBgColor = $derived(
    'bg-red-100 dark:bg-red-900/30 border-red-200/40 dark:border-red-800/30',
  );
  const iconColor = $derived('text-red-600 dark:text-red-400');

  async function handleConfirm() {
    try {
      isDeleting = true;
      await onDelete(tag);
      open = false;
    } catch (error) {
      console.error('Failed to delete tag:', error);
    } finally {
      isDeleting = false;
    }
  }

  function handleCancel() {
    open = false;
  }
</script>

<Popover.Root bind:open>
  <Popover.Trigger>
    {#snippet child({ props })}
      <button
        {...props}
        type="button"
        class="group relative flex items-center justify-center h-8 w-8 rounded-md bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 hover:border-red-200 dark:hover:border-red-800/50 active:scale-95 focus-visible:ring-red-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transition-all duration-200 ease-out disabled:cursor-not-allowed disabled:opacity-50"
        aria-label="Delete tag"
      >
        <Icon icon="heroicons:trash" class="h-4 w-4" />
        <span class="sr-only">Delete tag "{tag.name}"</span>
      </button>
    {/snippet}
  </Popover.Trigger>

  <Popover.Content class="w-80" side="left" align="center">
    <div class="w-full max-w-sm p-2 space-y-4">
      <div class="flex items-center gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-full {iconBgColor} border shadow-sm flex-shrink-0"
        >
          <Icon icon={iconName} class="h-5 w-5 {iconColor}" />
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {title}
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {description}
          </p>
        </div>
      </div>

      {#if hasImages || hasChildren}
        <div
          class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-3"
        >
          <h4
            class="text-xs font-medium text-amber-800 dark:text-amber-200 mb-1"
          >
            Deleting this tag will also:
          </h4>
          <ul class="text-xs text-amber-700 dark:text-amber-300 space-y-0.5">
            {#if hasImages}
              <li>• Remove tag from {tag.imageCount} images</li>
            {/if}
            {#if hasChildren}
              <li>• Delete {childrenCount} child tags</li>
            {/if}
          </ul>
        </div>
      {/if}

      <div class="flex gap-3 pt-2">
        <Button
          variant="glass"
          rounded="full"
          size="sm"
          onclick={handleCancel}
          class="flex-1"
          disabled={isDeleting}
        >
          Cancel
        </Button>
        <Button
          {variant}
          rounded="full"
          size="sm"
          onclick={handleConfirm}
          class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
          disabled={isDeleting}
        >
          {#if isDeleting}
            <Icon icon="eos-icons:three-dots-loading" class="h-4 w-4 mr-2" />
          {:else}
            <Icon icon={iconName} class="h-4 w-4 mr-2" />
          {/if}
          {confirmText}
        </Button>
      </div>
    </div>
  </Popover.Content>
</Popover.Root>
