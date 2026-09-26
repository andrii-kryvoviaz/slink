import { themeIcons } from '@slink/theme.icons';
import { error, redirect } from '@sveltejs/kit';

import { browser } from '$app/environment';
import { goto } from '$app/navigation';

import { type ApiClientType, createApiClient } from '@slink/api/Client';

import '@slink/lib/utils/i18n/messages/api.language';

import '@slink/utils/string/stringExtensions';
import { preloadIconSet } from '@slink/utils/ui/preloadIconSet';

export class Application {
  private static _api: ApiClientType;
  private static _bootstrapped: Promise<void> | null = null;
  private static _redirectingUnauthorized: Promise<void> | null = null;

  static get api(): ApiClientType {
    return this._api;
  }

  static async initialize(
    fetch: typeof globalThis.fetch,
    gatewayUrl: string,
  ): Promise<void> {
    this._api = createApiClient(fetch);
    this.registerApiEventHandlers(gatewayUrl);

    this._bootstrapped ??= this.bootstrap();
    return this._bootstrapped;
  }

  private static async bootstrap(): Promise<void> {
    await preloadIconSet(themeIcons);
  }

  private static registerApiEventHandlers(gatewayUrl: string): void {
    this.api.on('unauthorized', () => this.redirectToLogin(gatewayUrl));

    this.api.on('forbidden', () => {
      error(403, {
        message: 'You do not have permission to access this page.',
      });
    });
  }

  private static redirectToLogin(gatewayUrl: string): Promise<void> {
    if (!browser) {
      redirect(302, gatewayUrl);
    }

    this._redirectingUnauthorized ??= goto(gatewayUrl, {
      invalidateAll: true,
    }).finally(() => {
      this._redirectingUnauthorized = null;
    });

    return this._redirectingUnauthorized;
  }
}
