import { useApiKeyStore } from '$lib/state/ApiKeyStore.svelte.js';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { ApiKeyFormData } from './types';

export class ApiKeyService {
  private apiKeyStore = useApiKeyStore();

  async createApiKey(formData: ApiKeyFormData): Promise<boolean> {
    try {
      const data = {
        name: formData.name.trim(),
        ...(formData.expiresAt ? { expiresAt: formData.expiresAt } : {}),
      };

      await this.apiKeyStore.create(data);
      return true;
    } catch (error) {
      throw error;
    }
  }

  async revokeApiKey(keyId: string): Promise<boolean> {
    try {
      await this.apiKeyStore.revoke(keyId);
      return true;
    } catch (error) {
      return false;
    }
  }

  async downloadShareXConfig(): Promise<void> {
    try {
      const baseUrl = window.location.origin;

      if (!this.apiKeyStore.createdKey) {
        toast.error(
          'ShareX config can only be downloaded for newly created keys. Please create a new key to download the config.',
        );
        return;
      }

      const key = this.apiKeyStore.createdKey.key;
      await this.apiKeyStore.downloadShareXConfig(baseUrl, key);
    } catch (error) {
      console.error('Failed to download ShareX config:', error);
      toast.error('Failed to download ShareX config');
    }
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
    toast.success('Copied to clipboard');
  }
}
