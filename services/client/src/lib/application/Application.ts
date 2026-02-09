import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { goto } from '$app/navigation';
import { initializeDI } from '$lib/di/container';

import { type ApiClientType, createApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export class Application {
  private static isInitialized = false;
  private static initPromise: Promise<void> | null = null;
  private static _api: ApiClientType;

  public static get api(): ApiClientType {
    return Application._api;
  }

  async initialize(): Promise<void> {
    if (Application.isInitialized) {
      return;
    }

    if (Application.initPromise) {
      return Application.initPromise;
    }

    Application.initPromise = this.doInitialize();
    await Application.initPromise;
    Application.isInitialized = true;
  }

  private async doInitialize(): Promise<void> {
    this.setupDependencyInjection();
    this.setupApiClientEventHandlers();
    await this.preloadAssets();
  }

  setupApiClient(fetch: typeof globalThis.fetch): void {
    Application._api = createApiClient(fetch);
  }

  private setupDependencyInjection(): void {
    initializeDI();
  }

  private setupApiClientEventHandlers(): void {
    Application.api.on('unauthorized', () => {
      if (!browser) {
        redirect(302, '/profile/login');
      }

      goto('/profile/login');
    });

    Application.api.on('forbidden', () => {
      error(403, {
        message: 'You do not have permission to access this page.',
      });
    });
  }

  private async preloadAssets(): Promise<void> {
    await preloadIconSet(themeIcons);
  }
}
