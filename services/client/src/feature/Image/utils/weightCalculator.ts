import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

type ContentWeightFn = (item: ImageListingItem) => number;

export function getAspectRatio(item: ImageListingItem): number {
  const { width, height } = item.metadata;
  return width && height ? height / width : 1;
}

export function createWeightCalculator(
  ...contentWeightFns: ContentWeightFn[]
): (item: ImageListingItem) => number {
  return (item) =>
    contentWeightFns.reduce(
      (weight, fn) => weight + fn(item),
      getAspectRatio(item),
    );
}
