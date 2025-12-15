import { error, redirect } from '@sveltejs/kit';

import { ApiClient } from '@slink/api/Client';
import { ForbiddenException } from '@slink/api/Exceptions';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({
  params,
  fetch,
  locals,
  parent,
}) => {
  const parentData = await parent();

  if (!locals.user) {
    redirect(302, '/profile/login');
  }

  const licensingEnabled =
    parentData.globalSettings?.image?.enableLicensing ?? false;

  try {
    const [image, imageTags, licenses] = await Promise.all([
      ApiClient.use(fetch).image.getDetails(params.id),
      ApiClient.use(fetch).tag.getImageTags(params.id),
      licensingEnabled
        ? ApiClient.use(fetch).image.getLicenses()
        : Promise.resolve({ licenses: [] }),
    ]);

    return {
      image,
      imageTags: imageTags.data,
      licenses: licenses.licenses,
      licensingEnabled,
    };
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
