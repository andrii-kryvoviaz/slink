import { AbstractResource } from '@slink/api/AbstractResource';
import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export interface Tag {
  id: string;
  name: string;
  path: string;
  parentId?: string | null;
  isRoot: boolean;
  depth: number;
  imageCount: number;
  createdAt?: string;
  updatedAt?: string;
  children?: Tag[];
}

export interface CreateTagRequest {
  name: string;
  parentId?: string;
}

export interface UpdateTagRequest {
  name?: string;
  parentId?: string | null;
}

export interface TagListRequest {
  limit?: number;
  orderBy?: 'name' | 'path' | 'createdAt' | 'updatedAt';
  order?: 'asc' | 'desc';
  page?: number;
  parentId?: string;
  searchTerm?: string;
  rootOnly?: boolean;
  includeChildren?: boolean;
}

export interface TagListingResponse {
  meta: ListingMetadata;
  data: Tag[];
}

export class TagResource extends AbstractResource {
  async getList(params: TagListRequest = {}): Promise<TagListingResponse> {
    return this.get('/tags', { query: params as Record<string, unknown> });
  }

  async create(data: CreateTagRequest): Promise<{ id: string }> {
    return this.post('/tags', { json: data });
  }

  async getById(id: string): Promise<Tag> {
    return this.get(`/tags/${id}`);
  }

  async getByIds(ids: string[]): Promise<Tag[]> {
    if (ids.length === 0) return [];

    const searchParams = new URLSearchParams();
    ids.forEach((id) => {
      searchParams.append('ids[]', id);
    });

    const response: TagListingResponse = await this.get(
      `/tags?${searchParams.toString()}`,
    );
    return response.data;
  }

  async deleteTag(id: string): Promise<void> {
    return this.delete(`/tags/${id}`);
  }

  async updateTag(id: string, data: UpdateTagRequest): Promise<void> {
    return this.patch(`/tags/${id}`, { json: data });
  }

  async tagImage(imageId: string, tagId: string): Promise<void> {
    return this.post(`/images/${imageId}/tags/${tagId}`);
  }

  async untagImage(imageId: string, tagId: string): Promise<void> {
    return this.delete(`/images/${imageId}/tags/${tagId}`);
  }

  async getImageTags(imageId: string): Promise<TagListingResponse> {
    return this.get(`/images/${imageId}/tags`);
  }
}
