import { type WeighableItem, createWeightCalculator } from './weightCalculator';

const PILL_ROW_WEIGHT = 0.1;

function pillRowWeight(item: WeighableItem): number {
  const hasPills =
    (item.tags?.length ?? 0) > 0 || (item.collections?.length ?? 0) > 0;
  return hasPills ? PILL_ROW_WEIGHT : 0;
}

export const calculateHistoryCardWeight = createWeightCalculator(pillRowWeight);
