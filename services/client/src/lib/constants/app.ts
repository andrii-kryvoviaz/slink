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
  COMMIT_HASH_LENGTH: 7,
  UNKNOWN: 'unknown',
} as const;

export const BUILD_GLOBALS = {
  APP_VERSION: '__APP_VERSION__',
  BUILD_DATE: '__BUILD_DATE__',
  COMMIT_HASH: '__COMMIT_HASH__',
} as const;
