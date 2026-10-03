import { updateService } from '#lib/services/update.service.js';
import type { UpdateCheckResult } from '#lib/services/update.service.js';

export type { VersionInfo } from './utils';
export type { GitHubRelease } from '#lib/services/github.service.js';
export type { UpdateCheckResult } from '#lib/services/update.service.js';
export { getVersionInfo, formatVersion } from './utils';

export function checkForUpdates(
  currentVersion: string,
): Promise<UpdateCheckResult> {
  return updateService.checkForUpdates(currentVersion);
}
