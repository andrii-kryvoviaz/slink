import type { Tag } from '@slink/api/Resources/TagResource';

export type TreeNode = {
  tag: Tag;
  children: string[];
  isLoaded: boolean;
  isLoading: boolean;
  hasChildren: boolean | null;
};

export type TreeRow = {
  node: TreeNode;
  depth: number;
};

export type ColumnVisibility = {
  name: boolean;
  imageCount: boolean;
  children: boolean;
  actions: boolean;
};

export type ColumnClassNames = {
  name?: string;
  imageCount?: string;
  children?: string;
  actions?: string;
};
