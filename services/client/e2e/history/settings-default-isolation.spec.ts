import { expect, test } from '../fixtures/auth.fixture';
import { stripSsrComments } from '../helpers/ssr';
import { HistoryPage } from '../pages/HistoryPage';

const DEFAULT_PAGE_SIZE = 12;
const COOKIE_PAGE_SIZE = 96;

function ssrPageSize(html: string): string | null {
  const stripped = stripSsrComments(html);
  const match = stripped.match(
    /<button\b[^>]*>(?:(?!<\/button>)[\s\S])*?Limit\s*(\d+)/,
  );
  return match ? match[1] : null;
}

test.describe('History table pageSize default is not shared across requests', () => {
  test('a settings.table cookie on one request does not change the next request default', async ({
    page,
    historyPage,
    layoutControls,
  }) => {
    await layoutControls.setSettingCookie(
      'table',
      JSON.stringify({ history: { pageSize: COOKIE_PAGE_SIZE } }),
    );

    const cookieHtml = await (await page.request.get(HistoryPage.URL)).text();
    expect(ssrPageSize(cookieHtml)).toBe(String(COOKIE_PAGE_SIZE));

    await page.goto(HistoryPage.URL);
    await expect(historyPage.pageSizeButton(COOKIE_PAGE_SIZE)).toBeVisible();

    await page.context().clearCookies({ name: 'settings.table' });

    const defaultHtml = await (await page.request.get(HistoryPage.URL)).text();
    expect(ssrPageSize(defaultHtml)).toBe(String(DEFAULT_PAGE_SIZE));

    await page.goto(HistoryPage.URL);
    await expect(historyPage.pageSizeButton(DEFAULT_PAGE_SIZE)).toBeVisible();
  });
});
