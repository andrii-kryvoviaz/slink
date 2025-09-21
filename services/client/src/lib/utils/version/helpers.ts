import { VERSION } from '$lib/constants/app';

export function cleanVersion(version: string): string {
  if (!version || typeof version !== 'string') {
    throw new Error('Version must be a non-empty string');
  }
  return version.replace(new RegExp(`^${VERSION.PREFIX}`), '');
}

interface SemanticVersion {
  major: number;
  minor: number;
  patch: number;
  prerelease?: string;
  build?: string;
}

function parseVersion(version: string): SemanticVersion {
  const semverRegex =
    /^(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/;
  const match = version.match(semverRegex);

  if (!match) {
    throw new Error(`Invalid semantic version: ${version}`);
  }

  const [, major, minor, patch, prerelease, build] = match;

  return {
    major: parseInt(major, 10),
    minor: parseInt(minor, 10),
    patch: parseInt(patch, 10),
    prerelease,
    build,
  };
}

function comparePrereleases(a?: string, b?: string): number {
  if (!a && !b) return 0;
  if (!a && b) return 1;
  if (a && !b) return -1;

  const aParts = a!.split('.');
  const bParts = b!.split('.');
  const maxLength = Math.max(aParts.length, bParts.length);

  for (let i = 0; i < maxLength; i++) {
    const aPart = aParts[i];
    const bPart = bParts[i];

    if (aPart === undefined) return -1;
    if (bPart === undefined) return 1;

    const aIsNumeric = /^\d+$/.test(aPart);
    const bIsNumeric = /^\d+$/.test(bPart);

    if (aIsNumeric && bIsNumeric) {
      const diff = parseInt(aPart, 10) - parseInt(bPart, 10);
      if (diff !== 0) return diff;
    } else if (aIsNumeric) {
      return -1;
    } else if (bIsNumeric) {
      return 1;
    } else {
      const diff = aPart.localeCompare(bPart);
      if (diff !== 0) return diff;
    }
  }

  return 0;
}

export function compareVersions(version1: string, version2: string): number {
  try {
    const v1 = parseVersion(version1);
    const v2 = parseVersion(version2);

    const majorDiff = v1.major - v2.major;
    if (majorDiff !== 0) return majorDiff;

    const minorDiff = v1.minor - v2.minor;
    if (minorDiff !== 0) return minorDiff;

    const patchDiff = v1.patch - v2.patch;
    if (patchDiff !== 0) return patchDiff;

    return comparePrereleases(v1.prerelease, v2.prerelease);
  } catch (error) {
    console.warn(
      'Version comparison failed, falling back to string comparison:',
      error,
    );
    return version1.localeCompare(version2, undefined, { numeric: true });
  }
}
