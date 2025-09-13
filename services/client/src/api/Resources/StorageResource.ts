import { AbstractResource } from '@slink/api/AbstractResource';

export interface StorageUsageResponse {
  provider: string;
  usedBytes: number;
  totalBytes: number | null;
  fileCount: number;
  usagePercentage: number | null;
}

export class StorageResource extends AbstractResource {
  public async getUsage(): Promise<StorageUsageResponse> {
    return this.get('/storage/usage');
  }
}
