import { VERSION } from '#lib/constants/app.js';

import { cleanVersion, isSemver } from './helpers.js';

export interface VersionInfo {
  version: string;
}

export function getVersionInfo(serverVersion?: string): VersionInfo {
  return { version: serverVersion || VERSION.UNKNOWN };
}

export function formatVersion(info: VersionInfo): string {
  if (info.version === VERSION.UNKNOWN) {
    return '';
  }

  let formatted = info.version;

  if (isSemver(cleanVersion(info.version))) {
    formatted = `${VERSION.PREFIX}${info.version}`;
  }

  return formatted;
}
