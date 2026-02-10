import { ApiClient } from '@slink/api/Client';
import type { Tag } from '@slink/api/Resources/TagResource';

import { extractErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

import type { TreeNode, TreeRow } from './types';

const resolveHasChildren = (tag: Tag): boolean | null => {
  if (typeof tag.hasChildren === 'boolean') {
    return tag.hasChildren;
  }
  if (typeof tag.childrenCount === 'number') {
    return tag.childrenCount > 0;
  }
  if (Array.isArray(tag.children)) {
    return tag.children.length > 0;
  }
  return null;
};

const buildNode = (tag: Tag): TreeNode => ({
  tag,
  children: [],
  isLoaded: false,
  isLoading: false,
  hasChildren: resolveHasChildren(tag),
});

export const useTagTree = () => {
  let nodeMap = $state<Map<string, TreeNode>>(new Map());
  let rootIds = $state<string[]>([]);
  let expandedTagIds = $state<Set<string>>(new Set());
  let isLoadingRoots = $state(false);
  let rootError = $state<string | null>(null);

  const setNode = (id: string, updater: (node: TreeNode) => TreeNode) => {
    const nextMap = new Map(nodeMap);
    const current = nextMap.get(id);
    if (!current) return;
    nextMap.set(id, updater(current));
    nodeMap = nextMap;
  };

  const loadRootTags = async () => {
    isLoadingRoots = true;
    rootError = null;

    try {
      const response = await ApiClient.tag.getRootTags();
      const nextMap = new Map<string, TreeNode>();
      const nextRootIds: string[] = [];

      response.data.forEach((tag) => {
        nextRootIds.push(tag.id);
        nextMap.set(tag.id, buildNode(tag));
      });

      nodeMap = nextMap;
      rootIds = nextRootIds;
      expandedTagIds = new Set();
    } catch (error: unknown) {
      const message = extractErrorMessage(
        error,
        'Failed to load root tags. Please try again.',
      );
      rootError = message;
      toast.error(message);
    } finally {
      isLoadingRoots = false;
    }
  };

  const loadChildren = async (id: string) => {
    const node = nodeMap.get(id);
    if (!node || node.isLoading || node.isLoaded) {
      return;
    }

    setNode(id, (current) => ({ ...current, isLoading: true }));

    try {
      const response = await ApiClient.tag.getChildren(id);
      const childIds = response.data.map((child) => child.id);
      const nextMap = new Map(nodeMap);

      response.data.forEach((child) => {
        const existing = nextMap.get(child.id);
        nextMap.set(child.id, existing ? { ...existing, tag: child } : buildNode(child));
      });

      const updatedParent = nextMap.get(id);
      if (updatedParent) {
        nextMap.set(id, {
          ...updatedParent,
          children: childIds,
          isLoaded: true,
          isLoading: false,
          hasChildren: childIds.length > 0,
        });
      }

      nodeMap = nextMap;

      if (childIds.length === 0) {
        const nextExpanded = new Set(expandedTagIds);
        nextExpanded.delete(id);
        expandedTagIds = nextExpanded;
      }
    } catch (error: unknown) {
      const message = extractErrorMessage(
        error,
        'Failed to load child tags. Please try again.',
      );
      toast.error(message);
      setNode(id, (current) => ({ ...current, isLoading: false }));
    }
  };

  const toggleNode = (id: string) => {
    const node = nodeMap.get(id);
    if (!node) return;

    if (expandedTagIds.has(id)) {
      const next = new Set(expandedTagIds);
      next.delete(id);
      expandedTagIds = next;
      return;
    }

    const next = new Set(expandedTagIds);
    next.add(id);
    expandedTagIds = next;

    if (!node.isLoaded) {
      loadChildren(id);
    }
  };

  const treeRows = $derived.by(() => {
    const rows: TreeRow[] = [];

    const walk = (ids: string[], depth: number) => {
      ids.forEach((id) => {
        const node = nodeMap.get(id);
        if (!node) return;
        rows.push({ node, depth });

        if (expandedTagIds.has(id) && node.children.length > 0) {
          walk(node.children, depth + 1);
        }
      });
    };

    walk(rootIds, 0);
    return rows;
  });

  return {
    get nodeMap() {
      return nodeMap;
    },
    get rootIds() {
      return rootIds;
    },
    get expandedTagIds() {
      return expandedTagIds;
    },
    get isLoadingRoots() {
      return isLoadingRoots;
    },
    get rootError() {
      return rootError;
    },
    get treeRows() {
      return treeRows;
    },
    loadRootTags,
    loadChildren,
    toggleNode,
  };
};
