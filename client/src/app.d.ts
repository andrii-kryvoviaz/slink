// See https://kit.svelte.dev/docs/types#app
// for information about these interfaces
import type { User } from '@slink/lib/auth/Type/User';
import type { CookieSettings } from '@slink/lib/settings';

declare global {
  namespace App {
    // interface Error {}
    interface Locals {
      settings: CookieSettings;
      user: User | null;
    }
    // interface PageData {}
    // interface Platform {}
  }

  interface String {
    capitalizeFirstLetter(): string;
  }
}

export {};
