import { ApiClient } from '@slink/api';

import { ValidationException } from '@slink/api/Exceptions';
import type {
  ApiKeyResponse,
  CreateApiKeyRequest,
  CreateApiKeyResponse,
} from '@slink/api/Resources/ApiKeyResource';

import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

class ApiKeyStore extends AbstractHttpState<ApiKeyResponse[]> {
  private _apiKeys: ApiKeyResponse[] = $state([]);
  private _createdKey: CreateApiKeyResponse | null = $state(null);
  private _isCreating = $state(false);
  private _isRevoking = $state(false);
  private _isDownloadingConfig = $state(false);
  private _downloadCompleted = $state(false);

  public constructor() {
    super();
  }

  public get apiKeys(): ApiKeyResponse[] {
    return this._apiKeys;
  }

  public get createdKey(): CreateApiKeyResponse | null {
    return this._createdKey;
  }

  public get isEmpty(): boolean {
    return this._apiKeys.length === 0;
  }

  public get isCreating(): boolean {
    return this._isCreating;
  }

  public get isRevoking(): boolean {
    return this._isRevoking;
  }

  public get isDownloadingConfig(): boolean {
    return this._isDownloadingConfig;
  }

  public get downloadCompleted(): boolean {
    return this._downloadCompleted;
  }

  public async load(options: RequestStateOptions = {}): Promise<void> {
    await this.fetch(
      () => ApiClient.apiKey.getApiKeys(),
      (response: any) => {
        const data = Array.isArray(response) ? response : response.data || [];
        this._apiKeys = data;
        this.markDirty(false);
      },
      options,
    );
  }

  public async create(
    data: CreateApiKeyRequest,
  ): Promise<CreateApiKeyResponse | null> {
    this._isCreating = true;
    this._downloadCompleted = false;

    let createdKey: CreateApiKeyResponse | null;

    try {
      createdKey = await ApiClient.apiKey.createApiKey(data);
      this._createdKey = createdKey;
      toast.success('API key created successfully');

      this.markDirty(true);
      await this.load();

      return createdKey;
    } catch (error) {
      if (!(error instanceof ValidationException)) {
        toast.error('Failed to create API key');
      }
      throw error;
    } finally {
      this._isCreating = false;
    }
  }

  public async revoke(keyId: string): Promise<void> {
    this._isRevoking = true;

    try {
      await ApiClient.apiKey.revokeApiKey(keyId);

      this._apiKeys = this._apiKeys.filter((key) => key.id !== keyId);
      this.markDirty(false);

      toast.success('API key revoked successfully');
    } catch (error) {
      toast.error('Failed to revoke API key');
      throw error;
    } finally {
      this._isRevoking = false;
    }
  }

  public async downloadShareXConfig(
    baseUrl: string,
    apiKey?: string,
  ): Promise<void> {
    this._isDownloadingConfig = true;

    try {
      const config = await ApiClient.apiKey.getShareXConfig(baseUrl, apiKey);

      const blob = new Blob([JSON.stringify(config, null, 2)], {
        type: 'application/json',
      });

      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'slink-sharex-config.sxcu';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    } catch (error) {
      toast.error('Failed to generate ShareX config');
      throw error;
    } finally {
      this._isDownloadingConfig = false;
      this._downloadCompleted = true;
    }
  }

  public findById(id: string): ApiKeyResponse | undefined {
    return this._apiKeys.find((key) => key.id === id);
  }

  public clearCreatedKey(): void {
    this._createdKey = null;
  }

  public async refresh(options: RequestStateOptions = {}): Promise<void> {
    this.markDirty(true);
    await this.load(options);
  }
}

const API_KEY_STORE = Symbol('ApiKeyStore');

const apiKeyStore = new ApiKeyStore();

export const useApiKeyStore = (
  func: ((store: ApiKeyStore) => void) | undefined = undefined,
): ApiKeyStore => {
  if (func) {
    func(apiKeyStore);
  }

  return useState<ApiKeyStore>(API_KEY_STORE, apiKeyStore);
};
