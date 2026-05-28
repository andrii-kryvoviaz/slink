import type { CollectionResponse } from '@slink/api/Response';

import { graceful } from '@slink/utils/async/graceful';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ parent, params, locals }) => {
  const { user } = await parent();

  const [hasAny, collection] = await Promise.all([
    graceful(() => locals.api.collection.itemsExist(params.id), true),
    graceful<CollectionResponse | null>(
      () => locals.api.collection.getById(params.id),
      null,
    ),
  ]);

  return {
    user,
    collectionId: params.id,
    globalSettings: locals.globalSettings,
    hasAny,
    collection,
  };
};
