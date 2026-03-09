import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

import { createWeightCalculator } from './weightCalculator';

const TAG_ROW_WEIGHT = 0.1;
const COLLECTION_ROW_WEIGHT = 0.1;

function tagWeight(item: ImageListingItem): number {
  return (item.tags?.length ?? 0) > 0 ? TAG_ROW_WEIGHT : 0;
}

function collectionWeight(item: ImageListingItem): number {
  return (item.collections?.length ?? 0) > 0 ? COLLECTION_ROW_WEIGHT : 0;
}

export const calculateHistoryCardWeight = createWeightCalculator(
  tagWeight,
  collectionWeight,
);
