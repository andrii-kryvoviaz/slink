<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

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

  const getTagDisplayName = (tag: Tag) => {
    if (tag.isRoot) {
      return tag.name;
    }
    return tag.path.replace('#', '').replace(/\//g, ' › ');
  };

  const shouldShow = $derived(
    (creatingChildFor && childTagName.trim()) ||
      (canCreate && searchTerm.trim() && !creatingChildFor),
  );

  const tagName = $derived(() => {
    if (creatingChildFor && childTagName.trim()) {
      return `${getTagDisplayName(creatingChildFor)} › ${childTagName}`;
    }
    return searchTerm.trim();
  });

  const buttonText = $derived(() => {
    const name = tagName();
    return `Create "${name}"`;
  });
</script>

{#if shouldShow}
  <Button
    variant="ghost"
    size="sm"
    class="w-full justify-start gap-2 px-3 py-2 text-sm"
    loading={isCreating}
    onclick={onCreateTag}
  >
    {#snippet leftIcon()}
      <Icon icon="ph:plus" class="h-4 w-4" />
    {/snippet}

    {#snippet loadingIcon()}
      <Icon icon="ph:spinner" class="h-4 w-4 animate-spin" />
    {/snippet}

    <span class="flex-1 text-left">
      {buttonText()}
    </span>
  </Button>
{/if}
