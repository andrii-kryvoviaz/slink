import { inject, injectable } from 'tsyringe';

import { CACHE } from '$lib/constants/app';
import { cleanVersion, compareVersions } from '$lib/utils/version/helpers';

import { CacheService } from './cache.service';
import { GitHubService } from './github.service';
import type { GitHubRelease } from './github.service';

export interface UpdateCheckResult {
  hasUpdate: boolean;
  currentVersion: string;
  latestVersion?: string;
  releaseInfo?: GitHubRelease;
  error?: string;
}

@injectable()
export class UpdateService {
  private readonly cache: CacheService<GitHubRelease>;

  constructor(
    @inject('GitHubService') private readonly githubService: GitHubService,
  ) {
    this.cache = new CacheService(CACHE.VERSION_CHECK_KEY);
  }

  async checkForUpdates(currentVersion: string): Promise<UpdateCheckResult> {
    if (!currentVersion || typeof currentVersion !== 'string') {
      return this.createResult(currentVersion || 'unknown', {
        error: 'Invalid current version provided',
      });
    }

    let release = this.cache.get();

    if (!release) {
      try {
        release = await this.githubService.getLatestRelease();

        if (release.draft || release.prerelease) {
          return this.createResult(currentVersion, {
            error: 'Latest release is draft or prerelease',
          });
        }

        this.cache.set(release);
      } catch (error) {
        console.error('Failed to check for updates:', error);
        return this.createResult(currentVersion, {
          error: error instanceof Error ? error.message : 'Unknown error',
        });
      }
    }

    if (!release.tag_name) {
      return this.createResult(currentVersion, {
        error: 'Release tag name is missing',
      });
    }

    try {
      const latestVersion = cleanVersion(release.tag_name);
      const currentVersionClean = cleanVersion(currentVersion);
      const hasUpdate = compareVersions(currentVersionClean, latestVersion) < 0;

      return this.createResult(currentVersion, {
        hasUpdate,
        latestVersion,
        releaseInfo: release,
      });
    } catch (error) {
      console.error('Failed to process versions:', error);
      return this.createResult(currentVersion, {
        error:
          error instanceof Error ? error.message : 'Version processing failed',
      });
    }
  }

  clearCache(): void {
    this.cache.clear();
  }

  private createResult(
    currentVersion: string,
    data: Partial<UpdateCheckResult>,
  ): UpdateCheckResult {
    return {
      hasUpdate: false,
      currentVersion: cleanVersion(currentVersion),
      ...data,
    };
  }
}
