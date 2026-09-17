import { expect, test } from '../fixtures/auth.fixture';
import { HistoryPage } from '../pages/HistoryPage';

const BASE_URL = process.env.E2E_BASE_URL ?? 'http://localhost:3100';
const DEFAULT_PAGE_SIZE = 12;
const COOKIE_PAGE_SIZE = 96;

function tablePageSizeCookie(pageSize: number) {
  return {
    name: 'settings.table',
    value: JSON.stringify({ history: { pageSize } }),
    url: BASE_URL,
  };
}

function pageSizeButton(pageSize: number) {
  return { name: `Limit ${pageSize}` };
}

function ssrPageSize(html: string): string | null {
  const stripped = html.replace(/<!--[\s\S]*?-->/g, '');
  const match = stripped.match(/Limit\s*(\d+)/);
  return match ? match[1] : null;
}

test.describe('History table pageSize default is not shared across requests', () => {
  test('a settings.table cookie on one request does not change the next request default', async ({
    page,
  }) => {
    await page.context().addCookies([tablePageSizeCookie(COOKIE_PAGE_SIZE)]);

    const cookieHtml = await (await page.request.get(HistoryPage.URL)).text();
    expect(ssrPageSize(cookieHtml)).toBe(String(COOKIE_PAGE_SIZE));

    await page.goto(HistoryPage.URL);
    await expect(
      page.getByRole('button', pageSizeButton(COOKIE_PAGE_SIZE)),
    ).toBeVisible();

    await page.context().clearCookies({ name: 'settings.table' });

    const defaultHtml = await (await page.request.get(HistoryPage.URL)).text();
    expect(ssrPageSize(defaultHtml)).toBe(String(DEFAULT_PAGE_SIZE));

    await page.goto(HistoryPage.URL);
    await expect(
      page.getByRole('button', pageSizeButton(DEFAULT_PAGE_SIZE)),
    ).toBeVisible();
  });
});
