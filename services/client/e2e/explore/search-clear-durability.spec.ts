import { expect, test } from '../fixtures/auth.fixture';
import { captureListingRequests } from '../helpers/listingRequests';

const HOLD_WINDOW_MS = 5000;
const TERM = 'zzznonexistentqueryzzz';

test.describe('Explore search clear durability', () => {
  test('a cleared search does not snap back after five seconds', async ({
    page,
    explorePage,
    api,
  }) => {
    await api.content.uploadImage({ isPublic: true });

    await page.goto(`/explore?search=${TERM}&searchBy=user`);
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toBeVisible();

    const listings = captureListingRequests(page);
    await page.getByRole('button', { name: 'Clear', exact: true }).click();

    await expect(explorePage.feedItems.first()).toBeVisible();
    await page.waitForTimeout(HOLD_WINDOW_MS);

    await expect(explorePage.searchInput).toHaveValue('');
    await expect(explorePage.feedItems.first()).toBeVisible();
    await expect(
      page.getByRole('heading', { name: 'No images found' }),
    ).toHaveCount(0);

    const params = new URL(page.url()).searchParams;
    expect(params.get('search')).toBeNull();
    expect(params.get('searchBy')).toBeNull();
    expect(
      listings.filter((url) => url.includes('searchTerm=')),
      `late refiltering requests: ${JSON.stringify(listings)}`,
    ).toHaveLength(0);
    expect(listings, JSON.stringify(listings)).toHaveLength(1);
  });
});
