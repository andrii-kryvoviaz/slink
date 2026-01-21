import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  CollectionItemsResponse,
  CollectionListingResponse,
  CollectionResponse,
} from '@slink/api/Response';
import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

export interface CreateCollectionRequest {
  name: string;
  description?: string;
}

export interface UpdateCollectionRequest {
  name?: string;
  description?: string;
}

export class CollectionResource extends AbstractResource {
  public async create(
    request: CreateCollectionRequest,
  ): Promise<CollectionResponse> {
    return this.post('/collection', { json: request });
  }

  public async getList(page: number = 1): Promise<CollectionListingResponse> {
    return this.get(`/collections/${page}`);
  }

  public async getById(collectionId: string): Promise<CollectionResponse> {
    return this.get(`/collection/${collectionId}`);
  }

  public async update(
    collectionId: string,
    request: UpdateCollectionRequest,
  ): Promise<CollectionResponse> {
    return this.patch('/collection', {
      json: { id: collectionId, ...request },
    });
  }

  public async remove(
    collectionId: string,
    deleteImages: boolean = false,
  ): Promise<void> {
    return this.delete('/collection', {
      json: { id: collectionId, deleteImages },
    });
  }

  public async getItems(
    collectionId: string,
    page: number = 1,
  ): Promise<CollectionItemsResponse> {
    return this.get(`/collection/${collectionId}/items/${page}`);
  }

  public async addItem(collectionId: string, itemId: string): Promise<void> {
    return this.post(`/collection/${collectionId}/items/${itemId}`);
  }

  public async removeItem(collectionId: string, itemId: string): Promise<void> {
    return this.delete(`/collection/${collectionId}/items/${itemId}`);
  }

  public async reorderItems(
    collectionId: string,
    orderedItemIds: string[],
  ): Promise<void> {
    return this.patch('/collection/items/order', {
      json: { collectionId, orderedItemIds },
    });
  }

  public async share(collectionId: string): Promise<ShareResponse> {
    return this.post(`/collection/${collectionId}/share`);
  }
}
