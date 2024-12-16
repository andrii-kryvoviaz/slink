import type { PageServerLoad } from './$types';
import { ApiClient } from '@slink/api/Client';
import { ForbiddenException } from '@slink/api/Exceptions';
import { error, redirect } from '@sveltejs/kit';

export const load: PageServerLoad = async ({ params, locals, parent }) => {
  await parent();

  if (!locals.user) {
    throw redirect(302, '/profile/login');
  }

  try {
    const image = await ApiClient.image.getDetails(params.id);

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
