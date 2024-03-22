import { error, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { ForbiddenException } from '@slink/api/Exceptions';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, locals, parent }) => {
  await parent();

  if (!locals.user) {
    throw redirect(302, '/profile/login');
  }

  try {
    return ApiClient.image.getDetails(params.id);
  } catch (e: unknown) {
    if (e instanceof ForbiddenException) {
      error(e.status, {
        message: e.message,
        button: {
          text: 'Take me to my Uploads',
          href: '/history',
        },
      });
    }

    const message =
      e instanceof Error
        ? e.message
        : 'An error occurred. Please try again later.';

    error(500, message);
  }
};
