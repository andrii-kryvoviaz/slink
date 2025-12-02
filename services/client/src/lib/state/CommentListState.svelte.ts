import { browser } from '$app/environment';
import type { Readable } from 'svelte/store';

import { ApiClient } from '@slink/api/Client';
import type { CommentItem } from '@slink/api/Response';

import { CommentEventType } from '@slink/lib/enum/CommentEventType';
import { SortOrder } from '@slink/lib/enum/SortOrder';
import { MercureService } from '@slink/lib/services/mercure.service';
import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { IndexedList } from '@slink/lib/state/core/IndexedList.svelte';

interface CommentEvent {
  event: CommentEventType;
  comment?: CommentItem;
  commentId?: string;
}

interface CommentListParams {
  imageId: string;
  imageOwnerId: string;
  currentUserId: string | null;
  sortOrder: Readable<SortOrder>;
}

export class CommentListState extends AbstractHttpState<CommentItem[]> {
  private _list = new IndexedList<CommentItem>({
    sortKey: (c) => c.createdAt.timestamp,
  });
  private _replyingTo: CommentItem | null = $state(null);
  private _editingComment: CommentItem | null = $state(null);
  private _hasLoaded = $state(false);
  private _sortOrder: SortOrder = $state(SortOrder.Asc);
  private _unsubscribe: (() => void) | null = null;
  private _unsubscribeSortOrder: (() => void) | null = null;

  private constructor(
    private readonly imageId: string,
    private readonly imageOwnerId: string,
    private readonly currentUserId: string | null,
    sortOrder: Readable<SortOrder>,
  ) {
    super();
    this._unsubscribeSortOrder = sortOrder.subscribe((value) => {
      this._sortOrder = value;
    });
  }

  static create(params: CommentListParams): CommentListState | null {
    if (!browser) return null;
    return new CommentListState(
      params.imageId,
      params.imageOwnerId,
      params.currentUserId,
      params.sortOrder,
    );
  }

  async load(): Promise<void> {
    await this.fetch(
      () => ApiClient.comment.getComments(this.imageId).then((r) => r.data),
      (data) => {
        this._list.set(data);
        this._hasLoaded = true;
      },
    );

    if (browser && !this._unsubscribe) {
      this.subscribeToUpdates();
    }
  }

  destroy(): void {
    this._unsubscribe?.();
    this._unsubscribe = null;
    this._unsubscribeSortOrder?.();
    this._unsubscribeSortOrder = null;
  }

  get comments(): CommentItem[] {
    return this._sortOrder === SortOrder.Asc
      ? this._list.items
      : [...this._list.items].reverse();
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
    return this._list.isEmpty;
  }

  get count(): number {
    return this._list.size;
  }

  get hasCurrentUser(): boolean {
    return this.currentUserId !== null;
  }

  async createComment(content: string): Promise<void> {
    await ApiClient.comment.createComment(
      this.imageId,
      content,
      this._replyingTo?.id,
    );
    this.clearInputState();
  }

  async updateComment(commentId: string, content: string): Promise<void> {
    await ApiClient.comment.updateComment(commentId, content);
    this._editingComment = null;
  }

  async deleteComment(commentId: string): Promise<void> {
    await ApiClient.comment.deleteComment(commentId);
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

  private clearInputState(): void {
    this._replyingTo = null;
    this._editingComment = null;
  }

  private async subscribeToUpdates(): Promise<void> {
    this._unsubscribe = await MercureService.getInstance().subscribe(
      `comments/image/${this.imageId}`,
      (data) => this.handleSSEEvent(data as CommentEvent),
    );
  }

  private handleSSEEvent(event: CommentEvent): void {
    switch (event.event) {
      case CommentEventType.Created:
        this.handleCommentCreated(event.comment);
        break;
      case CommentEventType.Edited:
        this.handleCommentEdited(event.comment);
        break;
      case CommentEventType.Deleted:
        this.handleCommentDeleted(event.commentId);
        break;
    }
  }

  private handleCommentCreated(comment?: CommentItem): void {
    if (comment) this._list.insert(comment);
  }

  private handleCommentEdited(comment?: CommentItem): void {
    if (comment) this._list.update(comment.id, () => comment);
  }

  private handleCommentDeleted(commentId?: string): void {
    if (commentId)
      this._list.patch(commentId, { isDeleted: true } as Partial<CommentItem>);
  }
}
