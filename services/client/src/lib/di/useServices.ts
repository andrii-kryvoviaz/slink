import type { GitHubService } from '$lib/services/github.service';
import type { MultiUploadService } from '$lib/services/multi-upload.service';
import type { UpdateService } from '$lib/services/update.service';

import { DI_TOKENS, container } from './container';

export function useServices() {
  return {
    githubService: container.resolve<GitHubService>(DI_TOKENS.GitHubService),
    multiUploadService: container.resolve<MultiUploadService>(
      DI_TOKENS.MultiUploadService,
    ),
    updateService: container.resolve<UpdateService>(DI_TOKENS.UpdateService),
  };
}

export function useGitHubService(): GitHubService {
  return container.resolve<GitHubService>(DI_TOKENS.GitHubService);
}

export function useMultiUploadService(): MultiUploadService {
  return container.resolve<MultiUploadService>(DI_TOKENS.MultiUploadService);
}

export function useUpdateService(): UpdateService {
  return container.resolve<UpdateService>(DI_TOKENS.UpdateService);
}
