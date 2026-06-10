// See https://kit.svelte.dev/docs/types#app
// for information about these interfaces
import type { AppSidebarGroup } from '@slink/feature/Navigation/Sidebar/types';

import type { ApiClientType } from '@slink/api/Client';
import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

import type { CookieManager } from '@slink/lib/auth/CookieManager';
import type { User } from '@slink/lib/auth/Type/User';
import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';
import type { UploadPolicy } from '@slink/lib/settings/UploadPolicy';
import type { UserSettings } from '@slink/lib/settings/UserSettings.svelte';

declare global {
  namespace App {
    interface Error {
      status?: number;
      message?: string;
      button?: {
        text: string;
        href: string;
      };
    }
    interface Locals {
      api: ApiClientType;
      settings: UserSettings;
      globalSettings: GlobalSettings | null;
      user: User | null;
      userPreferences: UserPreferencesResponse | null;
      uploadPolicy: UploadPolicy;
      cookieManager: CookieManager;
      locale: string;
    }
    interface PageData {
      settings: UserSettings;
      userAgent: string;
      sidebarGroups?: AppSidebarGroup[];
      uploadPolicy?: UploadPolicy;
      user?: User | null;
    }
    // interface Platform {}
  }

  interface String {
    capitalizeFirstLetter(): string;
    toFormattedHtml(): string;
    decodeHtmlEntities(): string;
  }

  const __APP_VERSION__: string;
  const __BUILD_DATE__: string;
  const __COMMIT_HASH__: string;
}

export {};
