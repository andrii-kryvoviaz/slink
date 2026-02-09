import { error, redirect } from '@sveltejs/kit';

import { ForbiddenException, NotFoundException } from '@slink/api/Exceptions';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ params, locals, parent }) => {
  const parentData = await parent();

  if (!locals.user) {
    redirect(302, '/profile/login');
  }

  const licensingEnabled =
    parentData.globalSettings?.image?.enableLicensing ?? false;

  try {
    const [image, imageTags, licenses] = await Promise.all([
      locals.api.image.getDetails(params.id),
      locals.api.tag.getImageTags(params.id),
      licensingEnabled
        ? locals.api.image.getLicenses()
        : Promise.resolve({ licenses: [] }),
    ]);

    return {
      image,
      imageTags: imageTags.data,
      licenses: licenses.licenses,
      licensingEnabled,
    };
  } catch (e: unknown) {
    if (e instanceof NotFoundException) {
      redirect(302, '/history');
    }

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
