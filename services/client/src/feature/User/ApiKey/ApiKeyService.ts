import { t } from '$lib/i18n';
import { useApiKeyStore } from '$lib/state/ApiKeyStore.svelte.js';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
import { get } from 'svelte/store';

import type { ApiKeyFormData } from './types';

export class ApiKeyService {
  private apiKeyStore = useApiKeyStore();

  async createApiKey(formData: ApiKeyFormData): Promise<boolean> {
    const data = {
      name: formData.name.trim(),
      ...(formData.expiresAt ? { expiresAt: formData.expiresAt } : {}),
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
        toast.error(
          get(t)(
            'pages.integrations.api_keys.service.sharex_only_for_newly_created',
          ),
        );
        return;
      }

      const key = this.apiKeyStore.createdKey.key;
      await this.apiKeyStore.downloadShareXConfig(baseUrl, key);
    } catch (error) {
      console.error(
        get(t)('pages.integrations.api_keys.service.download_failed_console'),
        error,
      );
      toast.error(
        get(t)('pages.integrations.api_keys.service.download_failed'),
      );
    }
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
    toast.success(
      get(t)('pages.integrations.api_keys.service.copied_to_clipboard'),
    );
  }
}
