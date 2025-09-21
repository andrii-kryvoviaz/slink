import { injectable } from 'tsyringe';

import { GITHUB } from '$lib/constants/app';

export interface GitHubRelease {
  tag_name: string;
  name: string;
  body: string;
  published_at: string;
  html_url: string;
  prerelease: boolean;
  draft: boolean;
}

@injectable()
export class GitHubService {
  private readonly apiUrl: string;

  constructor() {
    this.apiUrl = `${GITHUB.API_BASE_URL}/repos/${GITHUB.REPO_OWNER}/${GITHUB.REPO_NAME}`;
  }

  async getLatestRelease(): Promise<GitHubRelease> {
    const response = await fetch(`${this.apiUrl}/releases/latest`);

    if (!response.ok) {
      throw new Error(
        `GitHub API responded with ${response.status}: ${response.statusText}`,
      );
    }

    return response.json();
  }
}
