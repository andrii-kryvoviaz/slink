<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import type { TagFeed } from '@slink/lib/state/TagFeed.svelte';
  import { getTagLastSegment, getTagParentPath } from '@slink/lib/utils/tag';

  import TagTreeNode from './TagTreeNode.svelte';
  import TagTreeRowMeta from './TagTreeRowMeta.svelte';
  import {
    tagTreeNodeVariants,
    tagTreeViewVariants,
  } from './TagTreeView.theme';

  interface Props {
    feed: TagFeed;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
  }

  let { feed, onDelete, onMove }: Props = $props();

  const containers = tagTreeViewVariants();
  const styles = tagTreeNodeVariants();
</script>

{#if feed.searching}
  <div class={containers.flatList()}>
    {#each feed.data as tag (tag.id)}
      {@const parentPath = getTagParentPath(tag)}
      <div class={styles.row()}>
        <div class={styles.toggle()}>
          <span class={styles.spacer()}></span>
          <Icon icon="lucide:tag" class={styles.icon()} />
          <span class={styles.name()}>{getTagLastSegment(tag)}</span>
          {#if parentPath}
            <span class={styles.path()}>{parentPath}</span>
          {/if}
        </div>
        <TagTreeRowMeta {tag} {onDelete} {onMove} />
      </div>
    {/each}
  </div>
{:else}
  <div class={containers.root()}>
    {#each feed.data as node (node.id)}
      <TagTreeNode {node} depth={0} {feed} {onDelete} {onMove} />
    {/each}
  </div>
{/if}
