import type { Tag } from '@slink/api/Resources/TagResource';

import { useState } from '@slink/lib/state/core/ContextAwareState';

class TagFilterState {
  public selectedTags = $state<Tag[]>([]);
  public requireAllTags = $state(false);
  public isExpanded = $state(false);

  public setSelectedTags(tags: Tag[]): void {
    this.selectedTags = [...tags];
  }

  public setRequireAllTags(value: boolean): void {
    this.requireAllTags = value;
  }

  public clear(): void {
    this.selectedTags = [];
    this.requireAllTags = false;
  }

  public syncFromExternal(tags: Tag[], requireAllTags: boolean): void {
    const tagsChanged =
      this.selectedTags.length !== tags.length ||
      this.selectedTags.some((tag, index) => tag.id !== tags[index]?.id);

    if (tagsChanged) {
      this.setSelectedTags(tags);
    }

    if (this.requireAllTags !== requireAllTags) {
      this.setRequireAllTags(requireAllTags);
    }
  }
}

const TAG_FILTER_STATE = Symbol('TagFilterState');

const defaultState = new TagFilterState();

export const useTagFilterState = (
  func: ((state: TagFilterState) => void) | undefined = undefined,
): TagFilterState => {
  if (func) {
    func(defaultState);
  }

  return useState<TagFilterState>(TAG_FILTER_STATE, defaultState);
};

export type { TagFilterState };
