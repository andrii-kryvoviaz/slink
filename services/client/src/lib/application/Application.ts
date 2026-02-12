import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { goto, invalidateAll } from '$app/navigation';
import { initializeDI } from '$lib/di/container';

import { type ApiClientType, createApiClient } from '@slink/api/Client';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export class Application {
  private static _api: ApiClientType;
  private static _bootstrapped: Promise<void> | null = null;

  static get api(): ApiClientType {
    return this._api;
  }

  static async initialize(fetch: typeof globalThis.fetch): Promise<void> {
    this._api = createApiClient(fetch);
    this.registerApiEventHandlers();

    this._bootstrapped ??= this.bootstrap();
    return this._bootstrapped;
  }

  private static async bootstrap(): Promise<void> {
    initializeDI();
    await preloadIconSet(themeIcons);
  }

  private static registerApiEventHandlers(): void {
    this.api.on('unauthorized', async () => {
      if (!browser) {
        redirect(302, '/profile/login');
      }

      await invalidateAll();
      goto('/profile/login');
    });

    this.api.on('forbidden', () => {
      error(403, {
        message: 'You do not have permission to access this page.',
      });
    });
  }
}
