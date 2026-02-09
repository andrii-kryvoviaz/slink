import { ApiClient } from '@slink/api';

import { normalizeTagName, tagExists, validateTagName } from '$lib/utils/tag';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { ReactiveState } from '@slink/api/ReactiveState';
import type {
  CreateTagRequest,
  Tag,
  TagListingResponse,
} from '@slink/api/Resources/TagResource';

const TAG_SEARCH_LIMIT = 50;

export function useTagOperations() {
  const tagOperations = ReactiveState<TagListingResponse>(
    (searchTerm?: string) =>
      ApiClient.tag.getList({
        searchTerm: searchTerm || undefined,
        limit: TAG_SEARCH_LIMIT,
        orderBy: 'path',
        order: 'asc',
        includeChildren: true,
      }),
    { debounce: 300 },
  );

  const tagCreation = ReactiveState<{ id: string }>((data: CreateTagRequest) =>
    ApiClient.tag.create(data),
  );

  const tagFetching = ReactiveState<Tag>((id: string) =>
    ApiClient.tag.getById(id),
  );

  const createTag = async (
    name: string,
    parentId?: string,
    availableTags: Tag[] = [],
  ) => {
    const validation = validateTagName(name);
    if (validation) {
      toast.error(validation.message);
      return;
    }

    const tagName = normalizeTagName(name);

    if (tagExists(tagName, availableTags)) {
      toast.error('Tag already exists');
      return;
    }

    try {
      const request: CreateTagRequest = { name: tagName };
      if (parentId) {
        request.parentId = parentId;
      }
      await tagCreation.run(request);
    } catch (error) {
      console.error('Error creating tag:', error);
      toast.error('Failed to create tag');
      tagCreation.reset();
      tagFetching.reset();
    }
  };

  return {
    loadTags: tagOperations.run,
    isLoadingTags: tagOperations.isLoading,
    tagsResponse: tagOperations.data,

    createTag,
    isCreatingTag: tagCreation.isLoading,
    createdTagId: tagCreation.data,
    resetCreateTag: tagCreation.reset,

    fetchCreatedTag: tagFetching.run,
    createdTag: tagFetching.data,
    resetCreatedTag: tagFetching.reset,
  };
}
