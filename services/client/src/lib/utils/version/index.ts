import { updateService } from '$lib/services/update.service';
import type { UpdateCheckResult } from '$lib/services/update.service';

export type { VersionInfo } from './utils';
export type { GitHubRelease } from '$lib/services/github.service';
export type { UpdateCheckResult } from '$lib/services/update.service';
export { getVersionInfo, formatVersion } from './utils';

export function checkForUpdates(
  currentVersion: string,
): Promise<UpdateCheckResult> {
  return updateService.checkForUpdates(currentVersion);
}
