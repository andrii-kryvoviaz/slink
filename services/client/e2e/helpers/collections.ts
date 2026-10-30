import { expect } from '@playwright/test';

import type { ApiClient } from './api';

export const COLLECTIONS_AHEAD = 55;

export const OLDER_COLLECTION = 'Archive Older';

export const FILLER_PATTERN = /^Filler \d+$/;

export const fillerCollectionName = (index: number) =>
  `Filler ${String(index).padStart(2, '0')}`;

const nextServerSecond = async (api: ApiClient) => {
  const current = await api.serverTime();

  await expect
    .poll(() => api.serverTime(), { intervals: [100] })
    .toBeGreaterThan(current);
};

export const seedCollectionsAhead = async (
  api: ApiClient,
  targetName: string,
  count = COLLECTIONS_AHEAD,
) => {
  const targetId = await api.content.createCollection({ name: targetName });
  const olderId = await api.content.createCollection({
    name: OLDER_COLLECTION,
  });
  await nextServerSecond(api);

  const fillerIds: string[] = [];
  for (let index = 1; index <= count; index++) {
    fillerIds.push(
      await api.content.createCollection({ name: fillerCollectionName(index) }),
    );
  }

  return { targetId, olderId, fillerIds };
};
