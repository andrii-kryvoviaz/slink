import type { ApiClient } from './api';

export const fillerTagName = (index: number) =>
  `a${String(index).padStart(2, '0')}`;

export const seedTags = async (
  api: ApiClient,
  fillerCount: number,
  targetName: string,
) => {
  for (let index = 1; index <= fillerCount; index++) {
    await api.content.createTag(fillerTagName(index));
  }

  return api.content.createTag(targetName);
};
