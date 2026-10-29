import { type Page } from '@playwright/test';

type UrlFilter = (url: URL) => boolean;

const anyPage: UrlFilter = () => true;
const firstPage: UrlFilter = (url) => !url.searchParams.has('cursor');

function captureGets(page: Page, pathname: string, accepts: UrlFilter) {
  const urls: string[] = [];

  page.on('request', (request) => {
    if (request.method() !== 'GET') return;
    const url = new URL(request.url());
    if (url.pathname !== pathname || !accepts(url)) return;
    urls.push(request.url());
  });

  return urls;
}

export function captureListingRequests(
  page: Page,
  pathname = '/api/images',
): string[] {
  return captureGets(page, pathname, anyPage);
}

export function captureFeedLoads(page: Page): string[] {
  return captureGets(page, '/api/images', firstPage);
}
