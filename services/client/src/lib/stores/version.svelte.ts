import { BUILD_GLOBALS } from '$lib/constants/app';
import type { VersionInfo } from '$lib/utils/version/utils';
import { writable } from 'svelte/store';

export const versionStore = writable<VersionInfo | null>(null);

export function setAppVersion(version: string) {
  versionStore.set({
    version,
    buildDate:
      typeof (globalThis as any)[BUILD_GLOBALS.BUILD_DATE] !== 'undefined'
        ? (globalThis as any)[BUILD_GLOBALS.BUILD_DATE]
        : import.meta.env.VITE_BUILD_DATE,
    commitHash:
      typeof (globalThis as any)[BUILD_GLOBALS.COMMIT_HASH] !== 'undefined'
        ? (globalThis as any)[BUILD_GLOBALS.COMMIT_HASH]
        : import.meta.env.VITE_COMMIT_HASH,
    environment: import.meta.env.MODE,
  });
}
