import 'reflect-metadata';
import { container } from 'tsyringe';

import { GitHubService } from '$lib/services/github.service';
import { UpdateService } from '$lib/services/update.service';
import { UploadService } from '$lib/services/upload.service';

export const DI_TOKENS = {
  GitHubService: 'GitHubService',
  UploadService: 'UploadService',
  UpdateService: 'UpdateService',
} as const;

export function initializeDI(): void {
  container.registerSingleton(DI_TOKENS.GitHubService, GitHubService);
  container.registerSingleton(DI_TOKENS.UploadService, UploadService);
  container.registerSingleton(DI_TOKENS.UpdateService, UpdateService);
}

export { container };
