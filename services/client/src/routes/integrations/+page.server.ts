import { redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ locals }) => {
  if (!locals.user) {
    throw redirect(302, '/');
  }

  return {
    user: locals.user,
    externalUploadAutoPublish:
      locals.userPreferences?.['image.externalUploadAutoPublish'] ?? false,
  };
};
