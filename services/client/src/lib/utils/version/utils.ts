import { BUILD_GLOBALS, VERSION } from '$lib/constants/app';

export interface VersionInfo {
  version: string;
  buildDate?: string;
  commitHash?: string;
  environment?: string;
}

function getGlobalValue(key: string, fallback?: string): string {
  switch (key) {
    case BUILD_GLOBALS.APP_VERSION:
      return typeof __APP_VERSION__ !== 'undefined'
        ? __APP_VERSION__
        : fallback || VERSION.UNKNOWN;
    case BUILD_GLOBALS.BUILD_DATE:
      return typeof __BUILD_DATE__ !== 'undefined'
        ? __BUILD_DATE__
        : fallback || VERSION.UNKNOWN;
    case BUILD_GLOBALS.COMMIT_HASH:
      return typeof __COMMIT_HASH__ !== 'undefined'
        ? __COMMIT_HASH__
        : fallback || VERSION.UNKNOWN;
    default:
      return typeof (globalThis as Record<string, unknown>)[key] !== 'undefined'
        ? ((globalThis as Record<string, unknown>)[key] as string)
        : fallback || VERSION.UNKNOWN;
  }
}

export function getVersionInfo(serverVersion?: string): VersionInfo {
  return {
    version: serverVersion || getGlobalValue(BUILD_GLOBALS.APP_VERSION),
    buildDate: getGlobalValue(
      BUILD_GLOBALS.BUILD_DATE,
      import.meta.env.VITE_BUILD_DATE,
    ),
    commitHash: getGlobalValue(
      BUILD_GLOBALS.COMMIT_HASH,
      import.meta.env.VITE_COMMIT_HASH,
    ),
    environment: import.meta.env.MODE,
  };
}

export function formatVersion(info: VersionInfo): string {
  if (info.version === VERSION.UNKNOWN) {
    return '';
  }

  let formatted = `${VERSION.PREFIX}${info.version}`;

  if (info.commitHash && info.commitHash !== VERSION.UNKNOWN) {
    formatted += ` (${info.commitHash.substring(0, VERSION.COMMIT_HASH_LENGTH)})`;
  }

  return formatted;
}
