import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { initializeDI } from '$lib/di/container';

import { ApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export class Application {
  private isInitialized = false;

  initialize(): void {
    if (this.isInitialized) {
      return;
    }

    this.setupDependencyInjection();
    this.setupApiClientEventHandlers();
    this.preloadAssets();

    this.isInitialized = true;
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

  private preloadAssets(): void {
    preloadIconSet(themeIcons);
  }
}
