// See https://kit.svelte.dev/docs/types#app
// for information about these interfaces
import type { AppSidebarGroup } from '@slink/feature/Navigation/Sidebar/types';

import type { CookieManager } from '@slink/lib/auth/CookieManager';
import type { User } from '@slink/lib/auth/Type/User';
import type { CookieSettings } from '@slink/lib/settings';
import type { GlobalSettings } from '@slink/lib/settings/Type/GlobalSettings';

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
      settings: CookieSettings;
      globalSettings: GlobalSettings | null;
      user: User | null;
      cookieManager: CookieManager;
    }
    interface PageData {
      userAgent: string;
      sidebarGroups?: AppSidebarGroup[];
    }
    // interface Platform {}
  }

  interface String {
    capitalizeFirstLetter(): string;
  }

  const __APP_VERSION__: string;
  const __BUILD_DATE__: string;
  const __COMMIT_HASH__: string;
}

export {};
