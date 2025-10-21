import { AbstractResource } from '@slink/api/AbstractResource';

export interface StorageUsageResponse {
  provider: string;
  usedBytes: number;
  totalBytes: number | null;
  fileCount: number;
  usagePercentage: number | null;
  cacheBytes: number;
  cacheFileCount: number;
}

export interface ClearCacheResponse {
  cleared: number;
  message: string;
}

export class StorageResource extends AbstractResource {
  public async getUsage(): Promise<StorageUsageResponse> {
    return this.get('/storage/usage');
  }

  public async clearCache(): Promise<ClearCacheResponse> {
    return this.delete('/storage/cache');
  }
}
