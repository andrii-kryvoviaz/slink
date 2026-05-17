import type { ShareListItemVariant } from '@slink/api/Response/Share/ShareListItemResponse';

export const hasVariantParams = (
  variant: ShareListItemVariant | null | undefined,
): boolean => {
  if (!variant) return false;
  return Boolean(
    variant.width || variant.height || variant.filter || variant.format,
  );
};
