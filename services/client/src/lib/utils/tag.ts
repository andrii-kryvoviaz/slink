import type { Tag } from '@slink/api/Resources/TagResource';

export const TAG_VALIDATION = {
  MAX_NAME_LENGTH: 50,
  NAME_PATTERN: /^[a-zA-Z0-9_-]+$/,
  MIN_NAME_LENGTH: 1,
} as const;

export interface TagValidationError {
  field: 'name';
  message: string;
}

export function normalizeTagName(name: string): string {
  return name.trim();
}

export function validateTagName(name: string): TagValidationError | null {
  const trimmedName = normalizeTagName(name);

  if (!trimmedName) {
    return { field: 'name', message: 'Tag name cannot be empty' };
  }

  if (trimmedName.length < TAG_VALIDATION.MIN_NAME_LENGTH) {
    return {
      field: 'name',
      message: `Tag name must be at least ${TAG_VALIDATION.MIN_NAME_LENGTH} character long`,
    };
  }

  if (trimmedName.length > TAG_VALIDATION.MAX_NAME_LENGTH) {
    return {
      field: 'name',
      message: `Tag name must be ${TAG_VALIDATION.MAX_NAME_LENGTH} characters or less`,
    };
  }

  if (!TAG_VALIDATION.NAME_PATTERN.test(trimmedName)) {
    return {
      field: 'name',
      message:
        'Tag name can only contain letters, numbers, hyphens, and underscores',
    };
  }

  return null;
}

export function tagExists(
  name: string,
  existingTags: { name: string }[],
): boolean {
  const normalizedName = normalizeTagName(name).toLowerCase();
  return existingTags.some((tag) => tag.name.toLowerCase() === normalizedName);
}

export function deduplicateTags<T extends { id: string }>(tags: T[]): T[] {
  const seen = new Set<string>();
  return tags.filter((tag) => {
    if (seen.has(tag.id)) return false;
    seen.add(tag.id);
    return true;
  });
}

function cleanTagPath(path: string): string {
  return path.startsWith('#') ? path.slice(1) : path;
}

export function getTagPathSegments(tag: Tag): string[] {
  if (!tag.path) return [tag.name];
  return cleanTagPath(tag.path).split('/').filter(Boolean);
}

export function isTagNested(tag: Tag): boolean {
  return getTagPathSegments(tag).length > 1;
}

export function getTagDisplayName(tag: Tag): string {
  if (tag.isRoot) return tag.name;
  return cleanTagPath(tag.path).replace(/\//g, ' › ');
}

export function getTagParentPath(tag: Tag): string {
  const segments = getTagPathSegments(tag);
  return segments.slice(0, -1).join(' › ');
}

export function getTagLastSegment(tag: Tag): string {
  const segments = getTagPathSegments(tag);
  return segments[segments.length - 1] || tag.name;
}
