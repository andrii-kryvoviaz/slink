import { checkForUpdates } from '$lib/utils/version';
import type { UpdateCheckResult, VersionInfo } from '$lib/utils/version';

class UpdateCheckStateClass {
  private _isLoading = $state(false);
  private _result = $state<UpdateCheckResult | null>(null);

  get isLoading() {
    return this._isLoading;
  }

  get result() {
    return this._result;
  }

  get hasUpdate() {
    return this._result?.hasUpdate ?? false;
  }

  get latestRelease() {
    return this._result?.releaseInfo ?? null;
  }

  async checkForUpdates(versionInfo: VersionInfo | null) {
    if (!versionInfo || this._isLoading) return;

    if (!versionInfo.version || typeof versionInfo.version !== 'string') {
      console.warn('Invalid version info provided:', versionInfo);
      return;
    }

    this._isLoading = true;
    try {
      this._result = await checkForUpdates(versionInfo.version);
      if (this._result.error) {
        console.warn('Update check failed:', this._result.error);
      }
    } catch (error) {
      console.error('Failed to check for updates:', error);
    } finally {
      this._isLoading = false;
    }
  }

  reset() {
    this._isLoading = false;
    this._result = null;
  }
}

export const UpdateCheckState = new UpdateCheckStateClass();
