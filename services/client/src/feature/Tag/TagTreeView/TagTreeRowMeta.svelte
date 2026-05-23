<script lang="ts">
  import { TagActionsCell } from '@slink/feature/Tag';

  import { goto } from '$app/navigation';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { tagFilterUtils } from '@slink/lib/utils/tag/tagFilterUrl';

  import { tagTreeNodeVariants } from './TagTreeView.theme';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
  }

  let { tag, onDelete, onMove }: Props = $props();

  const styles = tagTreeNodeVariants();

  const openInHistory = () => {
    goto(tagFilterUtils.buildHistoryUrl(tag));
  };
</script>

{#if tag.imageCount > 0}
  <button
    type="button"
    class={styles.count()}
    title="View images"
    onclick={openInHistory}
  >
    {tag.imageCount}
  </button>
{:else}
  <span class={styles.countEmpty()}>0</span>
{/if}
<div class={styles.actions()}>
  <TagActionsCell
    {tag}
    {onDelete}
    {onMove}
    variant="transparent"
    triggerClass="hover:bg-transparent focus:bg-transparent dark:hover:bg-transparent dark:focus:bg-transparent"
  />
</div>
