import { env } from '$env/dynamic/private';
import { error, redirect } from '@sveltejs/kit';

import { crawlerDetect } from '$lib/utils/http/CrawlerDetect';

import type { RequestHandler } from './$types';

function resolveUrl(origin: string, location: string): string {
  return location.startsWith('/') ? `${origin}${location}` : location;
}

async function resolveShortCode(
  code: string,
  apiUrl: string,
  fetch: typeof globalThis.fetch,
): Promise<string> {
  const response = await fetch(`${apiUrl}/i/${code}`, { redirect: 'manual' });

  if (response.status !== 302 && response.status !== 301) {
    error(response.status);
  }

  const location = response.headers.get('Location');
  if (!location) error(502);

  return location;
}

async function proxyUrl(
  location: string,
  apiUrl: string,
  fetch: typeof globalThis.fetch,
): Promise<Response> {
  const imageResponse = await fetch(`${apiUrl}${location}`);

  return new Response(imageResponse.body, {
    headers: {
      'Content-Type':
        imageResponse.headers.get('Content-Type') || 'application/octet-stream',
      'Cache-Control':
        imageResponse.headers.get('Cache-Control') ||
        'public, max-age=31536000',
    },
  });
}

export const GET: RequestHandler = async ({ params, fetch, request, url }) => {
  const apiUrl = env.API_URL || 'http://localhost:8080';
  const location = await resolveShortCode(params.code, apiUrl, fetch);

  if (!crawlerDetect.isCrawler(request.headers.get('User-Agent'))) {
    redirect(302, resolveUrl(url.origin, location));
  }

  return proxyUrl(location, apiUrl, fetch);
};
