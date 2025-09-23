import { AbstractResource } from '@slink/api/AbstractResource';
import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export interface Tag {
  id: string;
  name: string;
  path: string;
  isRoot: boolean;
  depth: number;
  imageCount: number;
  children?: Tag[];
}

export interface CreateTagRequest {
  name: string;
  parentId?: string;
}

export interface TagListRequest {
  limit?: number;
  orderBy?: 'name' | 'path' | 'created_at';
  order?: 'asc' | 'desc';
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

  async deleteTag(id: string): Promise<void> {
    return this.delete(`/tags/${id}`);
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
