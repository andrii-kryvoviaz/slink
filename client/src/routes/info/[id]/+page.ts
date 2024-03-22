import { error, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { UnauthorizedException } from '@slink/api/Exceptions';
import type { ImageDetailsResponse } from '@slink/api/Response';

import type { PageLoad } from './$types';

export const load: PageLoad = async ({ params, fetch, parent }) => {
  await parent();

  try {
    const response: ImageDetailsResponse = await ApiClient.image.getDetails(
      params.id
    );

    return response;
  } catch (e: any) {
    console.error(e);
    if (e instanceof UnauthorizedException) {
      throw redirect(302, '/profile/login');
    }

    if (e.status === 403) {
      error(e.status, {
        message: e.message,
        button: {
          text: 'Take me to my Uploads',
          href: '/history',
        },
      });
    }

    error(e.status, e.message);
  }
};
