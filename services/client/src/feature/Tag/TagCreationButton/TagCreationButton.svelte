<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  import { tagCreationButtonVariants } from './TagCreationButton.theme';

  interface Props {
    searchTerm?: string;
    creatingChildFor?: Tag | null;
    childTagName?: string;
    isCreating: boolean;
    onCreateTag: () => void;
    canCreate: boolean;
    highlighted?: boolean;
    variant?: 'default' | 'neon' | 'minimal';
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
      class="w-full flex items-center gap-2"
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
