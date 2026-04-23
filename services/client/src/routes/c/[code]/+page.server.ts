import { env } from '$env/dynamic/private';
import { error, redirect } from '@sveltejs/kit';

import type { PageServerLoad } from './$types';

type ResolveResult =
  | { kind: 'redirect'; location: string }
  | { kind: 'unavailable' };

async function resolveShortCode(
  code: string,
  apiUrl: string,
  fetch: typeof globalThis.fetch,
): Promise<ResolveResult> {
  const response = await fetch(`${apiUrl}/c/${code}`, {
    redirect: 'manual',
  });

  if (response.status === 302 || response.status === 301) {
    const location = response.headers.get(/* @wc-ignore */ 'Location');

    if (!location) {
      error(502);
    }

    return { kind: 'redirect', location };
  }

  return { kind: 'unavailable' };
}

function resolveUrl(origin: string, location: string): string {
  if (location.startsWith('/')) {
    return `${origin}${location}`;
  }

  return location;
}

export const load: PageServerLoad = async ({ params, fetch, url }) => {
  const apiUrl = env.API_URL || 'http://localhost:8080';
  const result = await resolveShortCode(params.code, apiUrl, fetch);

  if (result.kind === 'unavailable') {
    return {
      unavailable: true,
    };
  }

  redirect(302, resolveUrl(url.origin, result.location));
};
