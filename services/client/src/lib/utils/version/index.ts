import { useUpdateService } from '$lib/di';
import type { UpdateCheckResult } from '$lib/services/update.service';

export type { VersionInfo } from './utils';
export type { GitHubRelease } from '$lib/services/github.service';
export type { UpdateCheckResult } from '$lib/services/update.service';
export { getVersionInfo, formatVersion } from './utils';
export { UpdateService } from '$lib/services/update.service';

export function checkForUpdates(
  currentVersion: string,
): Promise<UpdateCheckResult> {
  const updateService = useUpdateService();
  return updateService.checkForUpdates(currentVersion);
}
