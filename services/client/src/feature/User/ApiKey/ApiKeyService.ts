import { useApiKeyStore } from '$lib/state/ApiKeyStore.svelte.js';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import type { ApiKeyFormData } from './types';

export class ApiKeyService {
  private apiKeyStore = useApiKeyStore();

  async createApiKey(formData: ApiKeyFormData): Promise<boolean> {
    const data = {
      name: formData.name.trim(),
      ...(formData.expiresAt
        ? { expiresAt: formData.expiresAt.toISOString() }
        : {}),
    };

    await this.apiKeyStore.create(data);
    return true;
  }

  async revokeApiKey(keyId: string): Promise<boolean> {
    try {
      await this.apiKeyStore.revoke(keyId);
      return true;
    } catch {
      return false;
    }
  }

  async downloadShareXConfig(): Promise<void> {
    try {
      const baseUrl = window.location.origin;

      if (!this.apiKeyStore.createdKey) {
        toast.error(messages.apiKey.failedToDownloadConfig);
        return;
      }

      const key = this.apiKeyStore.createdKey.key;
      await this.apiKeyStore.downloadShareXConfig(baseUrl, key);
    } catch (error) {
      console.error('Failed to download ShareX config:', error);
      toast.error(messages.apiKey.failedToDownloadConfig);
    }
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
    toast.success(messages.clipboard.copied);
  }
}
