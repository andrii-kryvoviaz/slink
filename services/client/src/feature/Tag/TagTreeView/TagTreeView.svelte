<script lang="ts">
  import TagActionsCell from '@slink/feature/Tag/TagDataTable/TagActionsCell.svelte';
  import TagCountCell from '@slink/feature/Tag/TagDataTable/TagCountCell.svelte';
  import TagNameCell from '@slink/feature/Tag/TagDataTable/TagNameCell.svelte';
  import * as Table from '@slink/ui/components/table';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagPathSegments } from '@slink/lib/utils/tag';

  type TreeNode = {
    tag: Tag;
    children: TreeNode[];
  };

  type ColumnVisibility = {
    name: boolean;
    imageCount: boolean;
    children: boolean;
    actions: boolean;
  };

  type ColumnClassNames = {
    name?: string;
    imageCount?: string;
    children?: string;
    actions?: string;
  };

  interface Props {
    tags: Tag[];
    onDelete: (tag: Tag) => Promise<void>;
    columnVisibility?: ColumnVisibility;
    columnClassNames?: ColumnClassNames;
  }

  let {
    tags,
    onDelete,
    columnVisibility = {
      name: true,
      imageCount: true,
      children: true,
      actions: true,
    },
    columnClassNames = {},
  }: Props = $props();

  let expandedTagIds = $state<Set<string>>(new Set());
  let hasAutoExpanded = $state(false);
  let lastTagSignature = $state('');

  const getTagPathKey = (tag: Tag): string => {
    const segments = getTagPathSegments(tag);
    if (segments.length === 0) {
      return tag.name;
    }
    return segments.join('/');
  };

  const getTagParentPathKey = (tag: Tag): string | null => {
    const segments = getTagPathSegments(tag);
    if (segments.length <= 1) return null;
    return segments.slice(0, -1).join('/');
  };

  const buildTree = (items: Tag[]) => {
    const nodeMap = new Map<string, TreeNode>();
    const pathToId = new Map<string, string>();

    const addTag = (tag: Tag) => {
      if (!nodeMap.has(tag.id)) {
        nodeMap.set(tag.id, { tag, children: [] });
        pathToId.set(getTagPathKey(tag), tag.id);
      }

      if (tag.children && tag.children.length > 0) {
        tag.children.forEach((child) => addTag(child));
      }
    };

    for (const tag of items) {
      addTag(tag);
    }

    const roots: TreeNode[] = [];

    for (const node of nodeMap.values()) {
      const parentPath = getTagParentPathKey(node.tag);
      const parentId = parentPath ? pathToId.get(parentPath) : undefined;

      if (parentId && nodeMap.has(parentId) && parentId !== node.tag.id) {
        nodeMap.get(parentId)?.children.push(node);
      } else {
        roots.push(node);
      }
    }

    const sortNodes = (
      nodes: TreeNode[],
      comparator: (a: TreeNode, b: TreeNode) => number,
    ) => {
      nodes.sort(comparator);

      for (const node of nodes) {
        if (node.children.length > 0) {
          sortNodes(node.children, comparator);
        }
      }
    };

    const comparator = (a: TreeNode, b: TreeNode) => {
      const aKey = getTagPathKey(a.tag).toLowerCase();
      const bKey = getTagPathKey(b.tag).toLowerCase();
      return aKey.localeCompare(bKey);
    };

    sortNodes(roots, comparator);

    return { roots, nodeMap };
  };

  const treeData = $derived.by(() => buildTree(tags));

  const treeRows = $derived.by(() => {
    const rows: Array<{ node: TreeNode; depth: number }> = [];

    const walk = (nodes: TreeNode[], depth: number) => {
      for (const node of nodes) {
        rows.push({ node, depth });

        if (node.children.length > 0 && expandedTagIds.has(node.tag.id)) {
          walk(node.children, depth + 1);
        }
      }
    };

    walk(treeData.roots, 0);

    return rows;
  });

  $effect(() => {
    const signature = tags.map((tag) => tag.id).join('|');
    if (signature !== lastTagSignature) {
      lastTagSignature = signature;
      hasAutoExpanded = false;
    }
  });

  const setsEqual = (a: Set<string>, b: Set<string>) => {
    if (a.size !== b.size) return false;
    for (const value of a) {
      if (!b.has(value)) return false;
    }
    return true;
  };

  $effect(() => {
    const nextExpanded = new Set(
      [...expandedTagIds].filter((id) => treeData.nodeMap.has(id)),
    );

    if (!hasAutoExpanded) {
      treeData.nodeMap.forEach((node, id) => {
        if (node.children.length > 0) {
          nextExpanded.add(id);
        }
      });
      hasAutoExpanded = true;
    }

    if (!setsEqual(nextExpanded, expandedTagIds)) {
      expandedTagIds = nextExpanded;
    }
  });

  const toggleNode = (id: string) => {
    const next = new Set(expandedTagIds);
    if (next.has(id)) {
      next.delete(id);
    } else {
      next.add(id);
    }
    expandedTagIds = next;
    hasAutoExpanded = true;
  };

  const isColumnVisible = (id: keyof ColumnVisibility) => {
    return columnVisibility?.[id] ?? false;
  };

  const getColumnClassName = (id: keyof ColumnClassNames) => {
    return columnClassNames?.[id] ?? '';
  };
</script>

{#if treeRows.length > 0}
  {#each treeRows as row (row.node.tag.id)}
    <Table.Row
      class="group/row border-slate-200/60 dark:border-slate-700/40 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-50 dark:hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-700/30 transition-colors duration-200"
    >
      {#if isColumnVisible('name')}
        <Table.Cell
          class="{getColumnClassName('name')} text-slate-700 dark:text-slate-300"
        >
          <div
            class="flex items-center gap-2 min-w-0"
            style:padding-left={`${row.depth * 20}px`}
          >
            {#if row.node.children.length > 0}
              <button
                type="button"
                onclick={() => toggleNode(row.node.tag.id)}
                class="flex h-6 w-6 items-center justify-center rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/50 transition-colors"
                aria-label={expandedTagIds.has(row.node.tag.id)
                  ? `Collapse ${row.node.tag.name}`
                  : `Expand ${row.node.tag.name}`}
              >
                <Icon
                  icon="lucide:chevron-right"
                  class={`h-4 w-4 transition-transform duration-200 ${
                    expandedTagIds.has(row.node.tag.id) ? 'rotate-90' : ''
                  }`}
                />
              </button>
            {:else}
              <span class="h-6 w-6"></span>
            {/if}
            <div class="min-w-0 flex-1">
              <TagNameCell tag={row.node.tag} />
            </div>
          </div>
        </Table.Cell>
      {/if}

      {#if isColumnVisible('imageCount')}
        <Table.Cell
          class="{getColumnClassName('imageCount')} text-slate-700 dark:text-slate-300"
        >
          <TagCountCell
            count={row.node.tag.imageCount}
            type="images"
            tag={row.node.tag}
          />
        </Table.Cell>
      {/if}

      {#if isColumnVisible('children')}
        <Table.Cell
          class="{getColumnClassName('children')} text-slate-700 dark:text-slate-300"
        >
          <TagCountCell
            count={row.node.children.length}
            type="children"
            tag={row.node.tag}
          />
        </Table.Cell>
      {/if}

      {#if isColumnVisible('actions')}
        <Table.Cell
          class="{getColumnClassName('actions')} text-slate-700 dark:text-slate-300"
        >
          <TagActionsCell tag={row.node.tag} {onDelete} />
        </Table.Cell>
      {/if}
    </Table.Row>
  {/each}
{/if}
