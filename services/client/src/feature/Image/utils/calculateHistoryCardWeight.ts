import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

import { createWeightCalculator } from './weightCalculator';

const TAGS_PER_ROW = 3;
const TAG_ROW_WEIGHT = 0.1;

function tagWeight(item: ImageListingItem): number {
  const tagCount = item.tags?.length ?? 0;
  return Math.ceil(tagCount / TAGS_PER_ROW) * TAG_ROW_WEIGHT;
}

export const calculateHistoryCardWeight = createWeightCalculator(tagWeight);
