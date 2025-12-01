import { AbstractResource } from '@slink/api/AbstractResource';
import type { CommentListingResponse } from '@slink/api/Response';

export class CommentResource extends AbstractResource {
  public async getComments(
    imageId: string,
    page: number = 1,
    limit: number = 50,
  ): Promise<CommentListingResponse> {
    const searchParams = new URLSearchParams({
      page: page.toString(),
      limit: limit.toString(),
    });

    return this.get(`/image/${imageId}/comments?${searchParams.toString()}`);
  }

  public async createComment(
    imageId: string,
    content: string,
    referencedCommentId?: string,
  ): Promise<void> {
    return this.post(`/image/${imageId}/comments`, {
      json: {
        content,
        ...(referencedCommentId && { referencedCommentId }),
      },
    });
  }

  public async updateComment(
    commentId: string,
    content: string,
  ): Promise<void> {
    return this.patch(`/comment/${commentId}`, {
      json: { content },
    });
  }

  public async deleteComment(commentId: string): Promise<void> {
    return this.delete(`/comment/${commentId}`);
  }
}
