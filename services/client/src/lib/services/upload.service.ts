import { ChunkedUpload } from '@slink/lib/services/ChunkedUpload';
import type { UploadItem } from '@slink/lib/services/ChunkedUpload';
import { randomId } from '@slink/lib/utils/string/randomId';

export type { UploadItem };

export interface UploadOptions {
  tagIds?: string[];
  collectionIds?: string[];
  onProgress?: (item: UploadItem) => void;
  onComplete?: (item: UploadItem) => void;
  onError?: (item: UploadItem, error: Error) => void;
}

class UploadService {
  private _uploads: Map<string, ChunkedUpload> = new Map();

  public createUploadItems(files: File[]): UploadItem[] {
    return files.map((file) => ({
      file,
      id: randomId('upload'),
      status: 'pending' as const,
      progress: 0,
    }));
  }

  public async uploadFiles(
    uploadItems: UploadItem[],
    options: UploadOptions = {},
  ): Promise<{ successful: UploadItem[]; failed: UploadItem[] }> {
    const {
      tagIds = [],
      collectionIds = [],
      onProgress,
      onComplete,
      onError,
    } = options;

    const uploadPromises = uploadItems.map(async (item) => {
      const upload = this._resolveUpload(item, {
        tagIds,
        collectionIds,
      });

      try {
        await upload.run(onProgress);
        onComplete?.(item);
      } catch (error) {
        if (item.status === 'error') {
          onError?.(item, item.errorDetails ?? (error as Error));
        }
      }
    });

    await Promise.allSettled(uploadPromises);

    const successful = uploadItems.filter(
      (item) => item.status === 'completed',
    );
    const failed = uploadItems.filter((item) => item.status === 'error');

    return { successful, failed };
  }

  public cancelAllUploads(): void {
    this._uploads.forEach((upload) => upload.cancel());
    this._uploads.clear();
  }

  private _resolveUpload(
    item: UploadItem,
    params: { tagIds: string[]; collectionIds: string[] },
  ): ChunkedUpload {
    const existing = this._uploads.get(item.id);
    if (existing) {
      return existing;
    }

    const upload = new ChunkedUpload(item, params);
    this._uploads.set(item.id, upload);
    return upload;
  }
}

export type { UploadService };

let _instance: UploadService;
export const uploadService = new Proxy({} as UploadService, {
  get: (_, prop) => Reflect.get((_instance ??= new UploadService()), prop),
});
