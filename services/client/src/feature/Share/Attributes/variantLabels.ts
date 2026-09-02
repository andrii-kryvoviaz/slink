import type { ShareListItemVariant } from '@slink/api/Response/Share/ShareListItemResponse';

export const dimensionsLabel = (
  variant: ShareListItemVariant | null | undefined,
): string | null => {
  if (!variant) return null;

  const { width, height } = variant;

  if (width && height) return `${width}×${height}`;
  if (width) return `${width}w`;
  if (height) return `${height}h`;

  return null;
};

export const formatLabel = (
  variant: ShareListItemVariant | null | undefined,
): string | null => variant?.format?.toUpperCase() ?? null;

export const filterLabel = (
  variant: ShareListItemVariant | null | undefined,
): string | null => {
  const raw = variant?.filter;

  if (!raw) return null;

  return raw.replace(/[-_]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};
