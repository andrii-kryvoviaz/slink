import type { VersionInfo } from '#lib/utils/version/utils.js';
import { writable } from 'svelte/store';

export const versionStore = writable<VersionInfo | null>(null);
