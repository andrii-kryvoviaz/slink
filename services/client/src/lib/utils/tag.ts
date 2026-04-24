import type { Tag } from '@slink/api/Resources/TagResource';

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
