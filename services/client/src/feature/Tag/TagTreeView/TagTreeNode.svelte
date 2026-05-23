<script lang="ts">
  import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
  } from '@slink/ui/components/collapsible';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import type { TagFeed } from '@slink/lib/state/TagFeed.svelte';
  import { getTagLastSegment } from '@slink/lib/utils/tag';

  import Self from './TagTreeNode.svelte';
  import TagTreeRowMeta from './TagTreeRowMeta.svelte';
  import { tagTreeNodeVariants } from './TagTreeView.theme';

  interface Props {
    node: Tag;
    depth: number;
    feed: TagFeed;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
  }

  let { node, depth, feed, onDelete, onMove }: Props = $props();

  const styles = $derived(tagTreeNodeVariants());

  const hasChildren = $derived(node.childCount > 0);
  const expanded = $derived(feed.isExpanded(node.id));
  const nodeLoading = $derived(feed.isNodeLoading(node.id));
  const children = $derived(feed.getChildren(node.id));
  const label = $derived(getTagLastSegment(node));

  const indent = $derived(`${depth * 1.25 + 0.25}rem`);
</script>

{#snippet meta()}
  <TagTreeRowMeta tag={node} {onDelete} {onMove} />
{/snippet}

{#if hasChildren}
  <Collapsible
    open={expanded}
    onOpenChange={() => feed.toggle(node)}
    class={styles.node()}
  >
    <div class={styles.row()} style:padding-left={indent}>
      <CollapsibleTrigger class={styles.toggle()}>
        <span class={styles.chevron()}>
          {#if nodeLoading}
            <Icon icon="lucide:loader-2" class="h-3.5 w-3.5 animate-spin" />
          {:else}
            <Icon
              icon="lucide:chevron-right"
              class={styles.chevronIcon({ expanded })}
            />
          {/if}
        </span>
        <Icon icon="lucide:tag" class={styles.icon()} />
        <span class={styles.name()}>{label}</span>
      </CollapsibleTrigger>
      {@render meta()}
    </div>

    <CollapsibleContent>
      {#each children as child (child.id)}
        <Self node={child} depth={depth + 1} {feed} {onDelete} {onMove} />
      {/each}
    </CollapsibleContent>
  </Collapsible>
{:else}
  <div class={styles.node()}>
    <div class={styles.row()} style:padding-left={indent}>
      <div class={styles.toggle()}>
        <span class={styles.spacer()}></span>
        <Icon icon="lucide:tag" class={styles.icon()} />
        <span class={styles.name()}>{label}</span>
      </div>
      {@render meta()}
    </div>
  </div>
{/if}
