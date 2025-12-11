import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

import { createWeightCalculator } from './weightCalculator';

const AVG_CHARS_PER_LINE = 40;
const MAX_VISIBLE_LINES = 2;
const LINE_WEIGHT = 0.05;

function descriptionWeight(item: ImageListingItem): number {
  const descLength = item.attributes.description?.trim().length ?? 0;
  const lineCount = Math.min(
    Math.ceil(descLength / AVG_CHARS_PER_LINE),
    MAX_VISIBLE_LINES,
  );
  return lineCount * LINE_WEIGHT;
}

export const calculateImageCardWeight =
  createWeightCalculator(descriptionWeight);
