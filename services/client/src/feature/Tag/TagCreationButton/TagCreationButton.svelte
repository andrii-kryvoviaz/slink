<script lang="ts">
  import { tagCreationButtonVariants } from '@slink/feature/Tag';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  interface Props {
    searchTerm?: string;
    creatingChildFor?: Tag | null;
    childTagName?: string;
    isCreating: boolean;
    onCreateTag: () => void;
    canCreate: boolean;
    highlighted?: boolean;
    variant?: 'default' | 'neon' | 'minimal' | 'subtle';
  }

  let {
    searchTerm = '',
    creatingChildFor = null,
    childTagName = '',
    isCreating,
    onCreateTag,
    canCreate,
    highlighted = false,
    variant = 'default',
  }: Props = $props();

  let shouldShow = $state(false);
  let tagName = $state('');
  let buttonText = $state('');

  $effect(() => {
    shouldShow =
      (creatingChildFor && childTagName.trim() !== '') ||
      (canCreate && searchTerm.trim() !== '' && !creatingChildFor);
  });

  $effect(() => {
    if (creatingChildFor && childTagName.trim()) {
      tagName = `${getTagDisplayName(creatingChildFor)} › ${childTagName}`;
    } else {
      tagName = searchTerm.trim();
    }
  });

  $effect(() => {
    buttonText = `Create "${tagName}"`;
  });
</script>

{#if shouldShow}
  <div
    class={tagCreationButtonVariants({
      variant,
      creating: isCreating,
      highlighted,
    })}
  >
    <button
      class="flex-1 flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all duration-300 text-gray-600 dark:text-white/80 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-500/30"
      onclick={onCreateTag}
      disabled={isCreating}
    >
      {#if isCreating}
        <Icon icon="ph:spinner" class="h-4 w-4 animate-spin" />
      {:else}
        <Icon icon="ph:plus" class="h-4 w-4" />
      {/if}
      <span class="truncate">
        {buttonText}
      </span>
    </button>
  </div>
{/if}
