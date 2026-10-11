export const GITHUB = {
  REPO_OWNER: 'andrii-kryvoviaz',
  REPO_NAME: 'slink',
  API_BASE_URL: 'https://api.github.com',
} as const;

export const CACHE = {
  VERSION_CHECK_KEY: 'slink_version_check',
  DURATION: {
    ONE_HOUR: 60 * 60 * 1000,
  },
} as const;

export const VERSION = {
  PREFIX: 'v',
  UNKNOWN: 'unknown',
} as const;
