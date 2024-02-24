// See https://kit.svelte.dev/docs/types#app
// for information about these interfaces
import type { CookieSettings } from '@slink/lib/settings';

declare global {
  namespace App {
    // interface Error {}
    interface Locals {
      settings: CookieSettings;
    }
    // interface PageData {}
    // interface Platform {}
  }

  interface String {
    capitalizeFirstLetter(): string;
  }
}

export {};
