import type { Tag } from '@slink/api/Resources/TagResource';

import { useState } from '@slink/lib/state/core/ContextAwareState';

class ImageTagManagerState {
  private _tagMap = $state<Map<string, Tag>>(new Map());
  private _version = $state(0);
  public isExpanded = $state(false);
  public imageId = $state<string>('');

  public assignedTags = $derived.by(() => {
    this._version;
    return Array.from(this._tagMap.values());
  });

  public get tagCount(): number {
    return this._tagMap.size;
  }

  public get isEmpty(): boolean {
    return this._tagMap.size === 0;
  }

  public initialize(imageId: string, initialTags: Tag[] = []): void {
    if (this.imageId === imageId) {
      return;
    }

    this.imageId = imageId;
    this.setAssignedTags(initialTags);
    this.isExpanded = false;
  }

  public setAssignedTags(tags: Tag[]): void {
    this._tagMap.clear();
    tags.forEach((tag) => this._tagMap.set(tag.id, tag));
    this._version++;
  }

  public removeTag(tagId: string): void {
    if (this._tagMap.delete(tagId)) {
      this._version++;
    }
  }

  public addTag(tag: Tag): void {
    this._tagMap.set(tag.id, tag);
    this._version++;
  }

  public hasTag(tagId: string): boolean {
    return this._tagMap.has(tagId);
  }

  public getTag(tagId: string): Tag | undefined {
    return this._tagMap.get(tagId);
  }

  public getTagIds(): Set<string> {
    return new Set(this._tagMap.keys());
  }

  public calculateTagChanges(newTags: Tag[]): {
    tagsToAdd: Tag[];
    tagsToRemove: Tag[];
    hasChanges: boolean;
  } {
    const newTagIds = new Set(newTags.map((tag) => tag.id));

    const tagsToAdd = newTags.filter((tag) => !this.hasTag(tag.id));
    const tagsToRemove = this.assignedTags.filter(
      (tag) => !newTagIds.has(tag.id),
    );

    return {
      tagsToAdd,
      tagsToRemove,
      hasChanges: tagsToAdd.length > 0 || tagsToRemove.length > 0,
    };
  }

  public updateTagsOptimistic(newTags: Tag[]): {
    tagsToAdd: Tag[];
    tagsToRemove: Tag[];
    hasChanges: boolean;
    rollback: () => void;
  } {
    const changes = this.calculateTagChanges(newTags);

    if (changes.hasChanges) {
      const previousTags = this.assignedTags;
      this.setAssignedTags(newTags);

      return {
        ...changes,
        rollback: () => this.setAssignedTags(previousTags),
      };
    }

    return {
      ...changes,
      rollback: () => {},
    };
  }

  public bulkUpdateTags(tagsToAdd: Tag[], tagIdsToRemove: string[]): void {
    let hasChanges = false;

    tagIdsToRemove.forEach((tagId) => {
      if (this._tagMap.delete(tagId)) {
        hasChanges = true;
      }
    });

    tagsToAdd.forEach((tag) => {
      this._tagMap.set(tag.id, tag);
      hasChanges = true;
    });

    if (hasChanges) {
      this._version++;
    }
  }

  public toggleExpanded(): void {
    this.isExpanded = !this.isExpanded;
  }

  public setExpanded(expanded: boolean): void {
    this.isExpanded = expanded;
  }

  public reset(): void {
    this._tagMap.clear();
    this._version++;
    this.isExpanded = false;
    this.imageId = '';
  }
}

const IMAGE_TAG_MANAGER_STATE = Symbol('ImageTagManagerState');

const defaultState = new ImageTagManagerState();

export const useImageTagManagerState = (
  func: ((state: ImageTagManagerState) => void) | undefined = undefined,
): ImageTagManagerState => {
  if (func) {
    func(defaultState);
  }

  return useState<ImageTagManagerState>(IMAGE_TAG_MANAGER_STATE, defaultState);
};

export type { ImageTagManagerState };
