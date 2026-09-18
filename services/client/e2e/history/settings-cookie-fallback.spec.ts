import { expect, test } from '../fixtures/auth.fixture';
import { HistoryPage } from '../pages/HistoryPage';

const malformedCookieCases: Array<{ label: string; value: string }> = [
  { label: 'tree viewMode', value: JSON.stringify({ viewMode: 'tree' }) },
  { label: 'bogus viewMode', value: JSON.stringify({ viewMode: 'bogus' }) },
];

test.describe('History settings cookie fallback', () => {
  for (const { label, value } of malformedCookieCases) {
    test(`recovers from a ${label} settings.history cookie`, async ({
      historyPage,
      layoutControls,
      api,
      page,
    }) => {
      await api.content.uploadImage();
      await layoutControls.setSettingCookie('history', value);

      await historyPage.goto();

      await expect(historyPage.viewModeOption('Table')).toHaveAttribute(
        'aria-checked',
        'true',
      );
      await expect(page.getByRole('button', { name: 'Columns' })).toBeVisible();
    });
  }
});
