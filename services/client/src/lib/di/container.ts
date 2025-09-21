import 'reflect-metadata';
import { container } from 'tsyringe';

import { GitHubService } from '$lib/services/github.service';
import { MultiUploadService } from '$lib/services/multi-upload.service';
import { UpdateService } from '$lib/services/update.service';

export const DI_TOKENS = {
  GitHubService: 'GitHubService',
  MultiUploadService: 'MultiUploadService',
  UpdateService: 'UpdateService',
} as const;

export function initializeDI(): void {
  container.registerSingleton(DI_TOKENS.GitHubService, GitHubService);
  container.registerSingleton(DI_TOKENS.MultiUploadService, MultiUploadService);
  container.registerSingleton(DI_TOKENS.UpdateService, UpdateService);
}

export { container };
