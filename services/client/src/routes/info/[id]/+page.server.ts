import { ApiClient } from '@slink/api/Client';
import { ForbiddenException } from '@slink/api/Exceptions';
import { error, redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({
  params,
  fetch,
  locals,
  parent,
}) => {
  await parent();

  if (!locals.user) {
    redirect(302, '/profile/login');
  }

  try {
    const image = await ApiClient.use(fetch).image.getDetails(params.id);

    return { image };
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
