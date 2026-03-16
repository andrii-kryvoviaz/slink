import type { GitHubService } from '$lib/services/github.service';
import type { UpdateService } from '$lib/services/update.service';
import type { UploadService } from '$lib/services/upload.service';

import { DI_TOKENS, container } from './container';

export function useServices() {
  return {
    githubService: container.resolve<GitHubService>(DI_TOKENS.GitHubService),
    uploadService: container.resolve<UploadService>(DI_TOKENS.UploadService),
    updateService: container.resolve<UpdateService>(DI_TOKENS.UpdateService),
  };
}

export function useGitHubService(): GitHubService {
  return container.resolve<GitHubService>(DI_TOKENS.GitHubService);
}

export function useUploadService(): UploadService {
  return container.resolve<UploadService>(DI_TOKENS.UploadService);
}

export function useUpdateService(): UpdateService {
  return container.resolve<UpdateService>(DI_TOKENS.UpdateService);
}
