import { ApiClient } from '@slink/api';

import {
  HttpException,
  PayloadTooLargeException,
  ValidationException,
} from '@slink/api/Exceptions';
import type {
  UploadChunkResponse,
  UploadCompleteResponse,
  UploadedImageResponse,
} from '@slink/api/Response';

import { extractShortErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

const CHUNK_TIMEOUT_MS = 30_000;

export interface UploadItem {
  file: File;
  id: string;
  status: 'pending' | 'uploading' | 'completed' | 'error' | 'cancelled';
  progress: number;
  result?: UploadedImageResponse;
  error?: string;
  errorDetails?: Error;
}

interface ChunkedUploadParams {
  tagIds: string[];
  collectionIds: string[];
}

interface UploadSession {
  uploadId: string;
  token: string;
  chunkSize: number;
  totalChunks: number;
  receivedChunks: Set<number>;
}

export class ChunkedUpload {
  private _abortController: AbortController | null = null;
  private _session: UploadSession | null = null;
  private _completedResult: UploadCompleteResponse | null = null;
  private _cancelled = false;
  private _timedOut = false;

  constructor(public readonly item: UploadItem) {}

  public async run(
    params: ChunkedUploadParams,
    onProgress?: (item: UploadItem) => void,
  ): Promise<void> {
    this._abortController = new AbortController();
    this._completedResult = null;
    this._cancelled = false;
    this._timedOut = false;

    const signal = this._abortController.signal;

    try {
      this.item.status = 'uploading';
      onProgress?.(this.item);

      const session = await this._resolveSession(params, signal);

      const result =
        this._completedResult ??
        (await this._uploadChunks(session, signal, onProgress));

      this.item.status = 'completed';
      this.item.progress = 100;
      this.item.result = result;
      this._session = null;
      this._completedResult = null;

      onProgress?.(this.item);
    } catch (error: unknown) {
      const errorInstance =
        error instanceof Error ? error : new Error(String(error));

      if (this._cancelled) {
        this.item.status = 'cancelled';
        this.item.error = messages.upload.cancelled;
        this.item.errorDetails = errorInstance;
        onProgress?.(this.item);
        throw errorInstance;
      }

      this.item.status = 'error';
      this.item.errorDetails = errorInstance;
      this.item.error = this._resolveErrorMessage(errorInstance);

      onProgress?.(this.item);
      throw errorInstance;
    } finally {
      this._abortController = null;
    }
  }

  public cancel(): void {
    this._cancelled = true;
    this._abortController?.abort();

    const session = this._session;
    if (!session) {
      return;
    }

    void ApiClient.image
      .abortUpload(session.uploadId, session.token)
      .catch(() => {});
  }

  private async _resolveSession(
    params: ChunkedUploadParams,
    signal: AbortSignal,
  ): Promise<UploadSession> {
    if (this._session) {
      const status = await ApiClient.image.getUploadStatus(
        this._session.uploadId,
        this._session.token,
        signal,
      );

      if (status.complete && status.id) {
        this._completedResult = { id: status.id };
        return this._session;
      }

      this._session.receivedChunks = new Set(status.receivedChunks);
      return this._session;
    }

    const { uploadId, token, chunkSize } = await ApiClient.image.initUpload(
      {
        fileName: this.item.file.name,
        totalSize: this.item.file.size,
        mimeType: this.item.file.type || 'application/octet-stream',
        tagIds: params.tagIds,
        collectionIds: params.collectionIds,
      },
      signal,
    );

    this._session = {
      uploadId,
      token,
      chunkSize,
      totalChunks: Math.max(1, Math.ceil(this.item.file.size / chunkSize)),
      receivedChunks: new Set(),
    };

    return this._session;
  }

  private async _uploadChunks(
    session: UploadSession,
    signal: AbortSignal,
    onProgress?: (item: UploadItem) => void,
  ): Promise<UploadCompleteResponse> {
    const { chunkSize, totalChunks } = session;
    const { file } = this.item;

    const missing: number[] = [];
    for (let index = 0; index < totalChunks; index++) {
      if (!session.receivedChunks.has(index)) {
        missing.push(index);
      }
    }

    if (missing.length === 0) {
      return this._completeUpload(session, totalChunks - 1, signal);
    }

    let completeResult: UploadCompleteResponse | null = null;

    for (let position = 0; position < missing.length; position++) {
      const index = missing[position];
      const isLast = position === missing.length - 1;

      const start = index * chunkSize;
      const end = Math.min(start + chunkSize, file.size);
      const blob = file.slice(start, end);

      const response = await this._putChunkWithTimeout(
        session.uploadId,
        index,
        blob,
        session.token,
        { complete: isLast },
        signal,
      );

      session.receivedChunks.add(index);

      if (isLast) {
        completeResult = response as UploadCompleteResponse;
      }

      this.item.progress = Math.round((end / file.size) * 100);
      onProgress?.(this.item);
    }

    return completeResult as UploadCompleteResponse;
  }

  private async _completeUpload(
    session: UploadSession,
    index: number,
    signal: AbortSignal,
  ): Promise<UploadCompleteResponse> {
    const { chunkSize } = session;
    const { file } = this.item;

    const start = index * chunkSize;
    const end = Math.min(start + chunkSize, file.size);
    const blob = file.slice(start, end);

    const response = await this._putChunkWithTimeout(
      session.uploadId,
      index,
      blob,
      session.token,
      { complete: true },
      signal,
    );

    return response as UploadCompleteResponse;
  }

  private async _putChunkWithTimeout(
    uploadId: string,
    index: number,
    chunk: Blob,
    token: string,
    options: { complete: boolean },
    parentSignal: AbortSignal,
  ): Promise<UploadChunkResponse | UploadCompleteResponse> {
    const timeoutController = new AbortController();
    const onParentAbort = () => timeoutController.abort();
    parentSignal.addEventListener('abort', onParentAbort, { once: true });

    const timeoutId = setTimeout(() => {
      this._timedOut = true;
      timeoutController.abort();
    }, CHUNK_TIMEOUT_MS);

    try {
      return await ApiClient.image.putChunk(
        uploadId,
        index,
        chunk,
        token,
        { complete: options.complete },
        timeoutController.signal,
      );
    } finally {
      clearTimeout(timeoutId);
      parentSignal.removeEventListener('abort', onParentAbort);
    }
  }

  private _resolveErrorMessage(error: Error): string {
    if (this._timedOut || error.name === 'TimeoutError') {
      return messages.upload.timedOut;
    }

    if (error.name === 'AbortError') {
      return messages.upload.connectionInterrupted;
    }

    if (error instanceof PayloadTooLargeException) {
      return messages.upload.tooLarge;
    }

    if (
      error instanceof HttpException &&
      (error.status === 401 || error.status === 403 || error.status === 410)
    ) {
      return messages.upload.sessionExpired;
    }

    if (error instanceof TypeError) {
      return messages.upload.connectionInterrupted;
    }

    if (error instanceof ValidationException && error.violations.length > 1) {
      return error.violations.map((v) => v.message).join('; ');
    }

    if (
      error instanceof HttpException &&
      Object.keys(error.errors).length > 1
    ) {
      return Object.values(error.errors)
        .map((e) => String(e))
        .join('; ');
    }

    return extractShortErrorMessage(error);
  }
}
