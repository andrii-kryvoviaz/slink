import { ApiClient } from '@slink/api';
import { injectable } from 'tsyringe';

import { HttpException, ValidationException } from '@slink/api/Exceptions';
import type { UploadedImageResponse } from '@slink/api/Response';

import { extractShortErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

export interface UploadItem {
  file: File;
  id: string;
  status: 'pending' | 'uploading' | 'completed' | 'error';
  progress: number;
  result?: UploadedImageResponse;
  error?: string;
  errorDetails?: Error;
}

export interface UploadOptions {
  isGuest?: boolean;
  tagIds?: string[];
  collectionIds?: string[];
  onProgress?: (item: UploadItem) => void;
  onComplete?: (item: UploadItem) => void;
  onError?: (item: UploadItem, error: Error) => void;
}

@injectable()
export class UploadService {
  private _abortControllers: Map<string, AbortController> = new Map();

  public createUploadItems(files: File[]): UploadItem[] {
    return files.map((file) => ({
      file,
      id: crypto.randomUUID(),
      status: 'pending' as const,
      progress: 0,
    }));
  }

  public async uploadFiles(
    uploadItems: UploadItem[],
    options: UploadOptions = {},
  ): Promise<{ successful: UploadItem[]; failed: UploadItem[] }> {
    const {
      isGuest = false,
      tagIds = [],
      collectionIds = [],
      onProgress,
      onComplete,
      onError,
    } = options;

    const uploadPromises = uploadItems.map((item) =>
      this._uploadSingleFile(
        item,
        isGuest,
        tagIds,
        collectionIds,
        onProgress,
        onComplete,
        onError,
      ),
    );

    await Promise.allSettled(uploadPromises);

    const successful = uploadItems.filter(
      (item) => item.status === 'completed',
    );
    const failed = uploadItems.filter((item) => item.status === 'error');

    return { successful, failed };
  }

  public cancelAllUploads(): void {
    this._abortControllers.forEach((controller) => controller.abort());
    this._abortControllers.clear();
  }

  private async _uploadSingleFile(
    item: UploadItem,
    isGuest: boolean,
    tagIds: string[],
    collectionIds: string[],
    onProgress?: (item: UploadItem) => void,
    onComplete?: (item: UploadItem) => void,
    onError?: (item: UploadItem, error: Error) => void,
  ): Promise<void> {
    const abortController = new AbortController();
    this._abortControllers.set(item.id, abortController);

    try {
      item.status = 'uploading';
      item.progress = 0;
      onProgress?.(item);

      const simulateProgress = this._createProgressSimulator(item, onProgress);
      simulateProgress.start();

      const result = await ApiClient.image.upload(item.file, {
        tagIds,
        collectionIds,
        asGuest: isGuest,
      });

      simulateProgress.complete();

      item.status = 'completed';
      item.progress = 100;
      item.result = result;

      onProgress?.(item);
      onComplete?.(item);
    } catch (error: unknown) {
      item.status = 'error';

      const errorInstance =
        error instanceof Error ? error : new Error(String(error));
      item.errorDetails = errorInstance;

      const fullMessage = extractShortErrorMessage(errorInstance);

      if (
        errorInstance instanceof ValidationException &&
        errorInstance.violations.length > 1
      ) {
        item.error = errorInstance.violations.map((v) => v.message).join('; ');
      } else if (
        errorInstance instanceof HttpException &&
        Object.keys(errorInstance.errors).length > 1
      ) {
        item.error = Object.values(errorInstance.errors)
          .map((e) => String(e))
          .join('; ');
      } else {
        item.error = fullMessage;
      }

      onProgress?.(item);
      onError?.(item, errorInstance);
    } finally {
      this._abortControllers.delete(item.id);
    }
  }

  private _createProgressSimulator(
    item: UploadItem,
    onProgress?: (item: UploadItem) => void,
  ) {
    let progressInterval: ReturnType<typeof setInterval> | null = null;
    let currentProgress = 0;

    return {
      start: () => {
        const fileSize = item.file.size;
        const uploadDuration = Math.min(
          Math.max((fileSize / (1024 * 1024)) * 2000, 1000),
          10000,
        );
        const progressIncrement = 90 / (uploadDuration / 100);

        progressInterval = setInterval(() => {
          if (currentProgress < 90) {
            currentProgress += progressIncrement;
            item.progress = Math.min(currentProgress, 90);
            onProgress?.(item);
          }
        }, 100);
      },
      complete: () => {
        if (progressInterval) {
          clearInterval(progressInterval);
          progressInterval = null;
        }
        currentProgress = 100;
        item.progress = 100;
      },
    };
  }
}
