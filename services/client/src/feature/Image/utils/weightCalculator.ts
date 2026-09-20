import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

type DeepPartial<T> = T extends object
  ? {
      [P in keyof T]?: DeepPartial<T[P]>;
    }
  : T;

export type WeighableItem = { id: string } & DeepPartial<
  Omit<ImageListingItem, 'id'>
>;

type ContentWeightFn = (item: WeighableItem) => number;

export function getAspectRatio(item: WeighableItem): number {
  const { width, height } = item.metadata ?? {};
  return width && height ? height / width : 1;
}

export function createWeightCalculator(
  ...contentWeightFns: ContentWeightFn[]
): <TItem extends WeighableItem>(item: TItem) => number {
  return (item) =>
    contentWeightFns.reduce(
      (weight, fn) => weight + fn(item),
      getAspectRatio(item),
    );
}
