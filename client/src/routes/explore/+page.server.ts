import { ApiClient } from '@slink/api/Client';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent }) => {
  await parent();

  const page = 1;
  const limit = 10;

  const { data: images, meta } = await ApiClient.image.getPublicImages(
    page,
    limit
  );

  return {
    images,
    meta,
    page,
    limit,
  };
};
