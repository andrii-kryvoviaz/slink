<script lang="ts">
  import { Button } from '@slink/ui/components/button';

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
  }

  let {
    searchTerm = '',
    creatingChildFor = null,
    childTagName = '',
    isCreating,
    onCreateTag,
    canCreate,
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
  <Button
    variant="transparent"
    size="sm"
    class="w-full justify-start mx-1 my-0.5"
    onclick={onCreateTag}
    loading={isCreating}
  >
    {#snippet leftIcon()}
      <Icon icon="ph:plus" class="h-4 w-4" />
    {/snippet}
    <span class="truncate">
      {buttonText}
    </span>
  </Button>
{/if}
