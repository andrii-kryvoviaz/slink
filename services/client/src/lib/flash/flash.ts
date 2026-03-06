import type { Cookies } from '@sveltejs/kit';

import type { CookieManager } from '@slink/lib/auth/CookieManager';

import type { toast } from '@slink/utils/ui/toast-sonner.svelte';

export type FlashType = keyof Pick<
  typeof toast,
  'error' | 'success' | 'warning' | 'info'
>;

export type FlashMessage = {
  type: FlashType;
  message: string;
};

const COOKIE_NAME = 'flash_messages';

export function createFlash(cookieManager: CookieManager, cookies: Cookies) {
  function parse(): FlashMessage[] {
    const raw = cookies.get(COOKIE_NAME);
    if (!raw) return [];
    try {
      return JSON.parse(raw) as FlashMessage[];
    } catch {
      return [];
    }
  }

  function set(type: FlashType, message: string) {
    const existing = parse();
    existing.push({ type, message });
    cookieManager.setCookie(cookies, COOKIE_NAME, JSON.stringify(existing), {
      httpOnly: true,
    });
  }

  return {
    error: (message: string) => set('error', message),
    success: (message: string) => set('success', message),
    warning: (message: string) => set('warning', message),
    info: (message: string) => set('info', message),
    consume(): FlashMessage[] {
      const messages = parse();
      if (messages.length) {
        cookieManager.deleteCookie(cookies, COOKIE_NAME);
      }
      return messages;
    },
  };
}

export type Flash = ReturnType<typeof createFlash>;
