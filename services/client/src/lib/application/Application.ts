import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { initializeDI } from '$lib/di/container';

import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export class Application {
  private static isInitialized = false;
  private static initPromise: Promise<void> | null = null;

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
    ApiClient.use(fetch);
  }

  private setupDependencyInjection(): void {
    initializeDI();
  }

  private setupApiClientEventHandlers(): void {
    ApiClient.on('unauthorized', () => {
      if (!browser) {
        redirect(302, '/profile/login');
      }
    });

    ApiClient.on('forbidden', () => {
      error(403, {
        message: 'You do not have permission to access this page.',
      });
    });
  }

  private async preloadAssets(): Promise<void> {
    await preloadIconSet(themeIcons);
  }
}
