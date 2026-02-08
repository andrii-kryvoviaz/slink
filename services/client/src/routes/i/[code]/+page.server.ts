import { env } from '$env/dynamic/private';
import { error, redirect } from '@sveltejs/kit';

import { crawlerDetect } from '$lib/utils/http/CrawlerDetect';

import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

import type { PageServerLoad } from './$types';

type OgMeta = {
  url: string;
  title: string;
  description: string;
  imageUrl: string;
  mimeType: string;
  width: string;
  height: string;
};

async function resolveShortCode(
  code: string,
  apiUrl: string,
  fetch: typeof globalThis.fetch,
): Promise<string> {
  const response = await fetch(`${apiUrl}/i/${code}`, {
    redirect: 'manual',
  });

  if (response.status !== 302 && response.status !== 301) {
    error(response.status);
  }

  const location = response.headers.get('Location');
  if (!location) {
    error(502);
  }

  return location;
}

function resolveUrl(origin: string, location: string): string {
  return location.startsWith('/') ? `${origin}${location}` : location;
}

async function buildOgMeta(
  location: string,
  code: string,
  origin: string,
  apiUrl: string,
  fetch: typeof globalThis.fetch,
): Promise<OgMeta> {
  const match = location.match(/\/image\/([^.]+)\.(\w+)/);
  if (!match) {
    redirect(302, resolveUrl(origin, location));
  }

  const [, imageId, ext] = match;

  const defaults: OgMeta = {
    url: `${origin}/i/${code}`,
    title: code,
    description: '',
    imageUrl: `${origin}/image/${imageId}.${ext}`,
    mimeType: `image/${ext}`,
    width: '',
    height: '',
  };

  try {
    const metaResponse = await fetch(`${apiUrl}/image/${imageId}/public`);
    if (metaResponse.ok) {
      const data: ImageListingItem = await metaResponse.json();
      return {
        ...defaults,
        title: data.attributes.fileName || code,
        description: data.attributes.description || '',
        mimeType: data.metadata.mimeType || defaults.mimeType,
        width: data.metadata.width ? String(data.metadata.width) : '',
        height: data.metadata.height ? String(data.metadata.height) : '',
      };
    }
  } catch {
    console.warn('Failed to fetch image metadata for OG tags, using defaults');
  }

  return defaults;
}

export const load: PageServerLoad = async ({ params, fetch, request, url }) => {
  const apiUrl = env.API_URL || 'http://localhost:8080';
  const location = await resolveShortCode(params.code, apiUrl, fetch);

  if (!crawlerDetect.isCrawler(request.headers.get('User-Agent'))) {
    redirect(302, resolveUrl(url.origin, location));
  }

  return {
    ogMeta: await buildOgMeta(location, params.code, url.origin, apiUrl, fetch),
  };
};
