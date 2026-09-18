import { type Page } from '@playwright/test';

export function captureListingRequests(
  page: Page,
  pathname = '/api/images',
): string[] {
  const urls: string[] = [];

  page.on('request', (request) => {
    if (request.method() !== 'GET') return;
    if (new URL(request.url()).pathname !== pathname) return;
    urls.push(request.url());
  });

  return urls;
}
