import { AbstractResource } from '@slink/api/AbstractResource';
import type {
  BookmarkListingResponse,
  BookmarkStatusResponse,
  BookmarkersResponse,
} from '@slink/api/Response';

export interface BookmarkToggleResponse {
  isBookmarked: boolean;
  bookmarkCount: number;
}

export class BookmarkResource extends AbstractResource {
  public async addBookmark(imageId: string): Promise<BookmarkToggleResponse> {
    return this.post(`/image/${imageId}/bookmark`);
  }

  public async removeBookmark(
    imageId: string,
  ): Promise<BookmarkToggleResponse> {
    return this.delete(`/image/${imageId}/bookmark`);
  }

  public async getBookmarkStatus(
    imageId: string,
  ): Promise<BookmarkStatusResponse> {
    return this.get(`/image/${imageId}/bookmark/status`);
  }

  public async getUserBookmarks(
    page: number = 1,
    limit: number = 10,
    cursor?: string,
  ): Promise<BookmarkListingResponse> {
    const searchParams = new URLSearchParams({
      limit: limit.toString(),
    });

    if (cursor) {
      searchParams.append('cursor', cursor);
    }

    return this.get(`/bookmarks/${page}?${searchParams.toString()}`);
  }

  public async getImageBookmarkers(
    imageId: string,
    page: number = 1,
    limit: number = 10,
    cursor?: string,
  ): Promise<BookmarkersResponse> {
    const searchParams = new URLSearchParams({
      limit: limit.toString(),
    });

    if (cursor) {
      searchParams.append('cursor', cursor);
    }

    return this.get(
      `/image/${imageId}/bookmarkers/${page}?${searchParams.toString()}`,
    );
  }
}
