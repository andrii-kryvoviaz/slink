import type { Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';

const ECHO_WINDOW_MS = 2500;

function captureListingRequests(page: Page): string[] {
  const urls: string[] = [];

  page.on('request', (request) => {
    if (request.method() !== 'GET') return;
    if (new URL(request.url()).pathname !== '/api/images') return;
    urls.push(request.url());
  });

  return urls;
}

test.describe('Explore search', () => {
  test('filters the feed by the search term', async ({ explorePage, api }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.search('zzznonexistentqueryzzz');

    await expect(explorePage.feedItems).toHaveCount(0);
  });

  test('shows the empty state for a term that matches nothing', async ({
    page,
    explorePage,
  }) => {
    await explorePage.goto();
    await explorePage.search('zzznonexistentqueryzzz');

    await expect(explorePage.feedItems).toHaveCount(0);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
  });

  test('a search arrival loads the feed exactly once', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore?search=zzznonexistentqueryzzz&searchBy=user');

    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
    await page.waitForTimeout(ECHO_WINDOW_MS);

    expect(listings).toHaveLength(1);
    expect(listings[0]).toContain('searchTerm=zzznonexistentqueryzzz');
    expect(listings[0]).toContain('searchBy=user');
    await expect(explorePage.searchInput).toHaveValue('zzznonexistentqueryzzz');
  });

  test('a plain arrival loads the feed exactly once', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore');

    await explorePage.feedItems.first().waitFor({ state: 'visible' });
    await page.waitForTimeout(ECHO_WINDOW_MS);

    expect(listings).toHaveLength(1);
    expect(listings[0]).not.toContain('searchTerm=');
  });

  test('a search without a scope in the url loads once and gains the default scope', async ({
    page,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    const listings = captureListingRequests(page);
    await page.goto('/explore?search=zzznonexistentqueryzzz');

    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();
    await page.waitForTimeout(ECHO_WINDOW_MS);

    expect(listings).toHaveLength(1);
    const params = new URL(page.url()).searchParams;
    expect(params.get('search')).toBe('zzznonexistentqueryzzz');
    expect(params.get('searchBy')).toBe('user');
  });

  test('clearing a search from the url empties the field and the params', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await page.goto('/explore?search=zzznonexistentqueryzzz&searchBy=user');
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();

    const listings = captureListingRequests(page);
    await page.getByRole('button', { name: 'Clear', exact: true }).click();

    await expect(explorePage.feedItems.first()).toBeVisible();
    await page.waitForTimeout(ECHO_WINDOW_MS);

    await expect(explorePage.searchInput).toHaveValue('');
    const params = new URL(page.url()).searchParams;
    expect(params.get('search')).toBeNull();
    expect(params.get('searchBy')).toBeNull();
    expect(listings.filter((url) => url.includes('searchTerm='))).toHaveLength(
      0,
    );
    expect(listings).toHaveLength(1);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toHaveCount(0);
  });

  test('a typed term writes the search into the url', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.searchInput.fill('zzznonexistentqueryzzz');

    await expect(async () => {
      const params = new URL(page.url()).searchParams;
      expect(params.get('search')).toBe('zzznonexistentqueryzzz');
      expect(params.get('searchBy')).toBe('user');
    }).toPass({ timeout: 15000 });

    await expect(explorePage.feedItems).toHaveCount(0);
    await expect(explorePage.searchInput).toBeFocused();
  });
});
