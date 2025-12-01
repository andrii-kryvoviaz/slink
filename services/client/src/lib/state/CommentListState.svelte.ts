import { browser } from '$app/environment';

import { ApiClient } from '@slink/api/Client';
import type { CommentItem } from '@slink/api/Response';

import { CommentEventType } from '@slink/lib/enum/CommentEventType';
import { MercureService } from '@slink/lib/services/mercure.service';
import type { CommentSortOrder } from '@slink/lib/settings/setters/comment';
import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';

type CommentEvent = {
  event: CommentEventType;
  comment?: CommentItem;
  commentId?: string;
};

export class CommentListState extends AbstractHttpState<CommentItem[]> {
  private _comments: CommentItem[] = $state([]);
  private _replyingTo: CommentItem | null = $state(null);
  private _editingComment: CommentItem | null = $state(null);
  private _hasLoaded = $state(false);
  private _unsubscribe: (() => void) | null = null;
  private _sortOrder: CommentSortOrder = $state('asc');

  constructor(
    private readonly imageId: string,
    private readonly imageOwnerId: string,
    private readonly currentUserId: string | null,
  ) {
    super();
  }

  set sortOrder(order: CommentSortOrder) {
    this._sortOrder = order;
  }

  async load(): Promise<void> {
    await this.fetch(
      () => ApiClient.comment.getComments(this.imageId).then((r) => r.data),
      (data) => {
        this._comments = data;
        this._hasLoaded = true;
      },
    );

    if (browser && !this._unsubscribe) {
      this.subscribeToUpdates();
    }
  }

  private async subscribeToUpdates(): Promise<void> {
    const mercure = MercureService.getInstance();
    this._unsubscribe = await mercure.subscribe(
      `comments/image/${this.imageId}`,
      (data) => this.handleSSEMessage(data as CommentEvent),
    );
  }

  private handleSSEMessage(data: CommentEvent): void {
    switch (data.event) {
      case CommentEventType.Created:
        if (data.comment) {
          this._comments = [...this._comments, data.comment];
        }
        break;
      case CommentEventType.Edited:
        if (data.comment) {
          this._comments = this._comments.map((c) =>
            c.id === data.comment!.id ? data.comment! : c,
          );
        }
        break;
      case CommentEventType.Deleted:
        if (data.commentId) {
          this._comments = this._comments.map((c) =>
            c.id === data.commentId ? { ...c, isDeleted: true } : c,
          );
        }
        break;
    }
  }

  async createComment(content: string): Promise<void> {
    await ApiClient.comment.createComment(
      this.imageId,
      content,
      this._replyingTo?.id,
    );
    this._replyingTo = null;
    this._editingComment = null;
  }

  async updateComment(commentId: string, content: string): Promise<void> {
    await ApiClient.comment.updateComment(commentId, content);
    this._editingComment = null;
  }

  async deleteComment(commentId: string): Promise<void> {
    await ApiClient.comment.deleteComment(commentId);
  }

  destroy(): void {
    this._unsubscribe?.();
    this._unsubscribe = null;
  }

  startReply(comment: CommentItem): void {
    this._replyingTo = comment;
    this._editingComment = null;
  }

  startEdit(comment: CommentItem): void {
    this._editingComment = comment;
    this._replyingTo = null;
  }

  cancelReply(): void {
    this._replyingTo = null;
  }

  cancelEdit(): void {
    this._editingComment = null;
  }

  isAuthor(comment: CommentItem): boolean {
    return (
      this.currentUserId !== null && comment.author.id === this.currentUserId
    );
  }

  canDelete(comment: CommentItem): boolean {
    if (!this.currentUserId || comment.isDeleted) return false;
    return (
      comment.author.id === this.currentUserId ||
      this.imageOwnerId === this.currentUserId
    );
  }

  canEdit(comment: CommentItem): boolean {
    return this.isAuthor(comment) && comment.canEdit;
  }

  isEditing(comment: CommentItem): boolean {
    return (
      this._editingComment !== null && this._editingComment.id === comment.id
    );
  }

  get comments(): CommentItem[] {
    const sorted = [...this._comments].sort((a, b) => {
      const timeA = a.createdAt.timestamp;
      const timeB = b.createdAt.timestamp;
      return this._sortOrder === 'asc' ? timeA - timeB : timeB - timeA;
    });
    return sorted;
  }

  get replyingTo(): CommentItem | null {
    return this._replyingTo;
  }

  get editingComment(): CommentItem | null {
    return this._editingComment;
  }

  get hasLoaded(): boolean {
    return this._hasLoaded;
  }

  get isEmpty(): boolean {
    return this._comments.length === 0;
  }

  get count(): number {
    return this._comments.length;
  }

  get hasCurrentUser(): boolean {
    return this.currentUserId !== null;
  }
}
