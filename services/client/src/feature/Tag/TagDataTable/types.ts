import type { Tag } from '@slink/api/Resources/TagResource';

export interface TagTableRow extends Tag {
  actions?: Record<string, unknown>;
}

export interface TagEditFormData {
  name: string;
  parentId?: string;
}

export interface TagCreateFormData {
  name: string;
  parentId?: string;
}

export type TagSortKey = 'name' | 'imageCount' | 'childrenCount';
export type TagSortOrder = 'asc' | 'desc';
