import { expect, test } from '../fixtures/auth.fixture';
import { IntegrationsPage } from '../pages/IntegrationsPage';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Created API key copy', () => {
  test('owner copies the created key, which stays selected and copied until the 2000ms reset', async ({
    page,
  }) => {
    const integrationsPage = new IntegrationsPage(page);

    await integrationsPage.gotoWithPausedClock(async () => {
      await integrationsPage.goto();
      await integrationsPage.createApiKey(`e2e-copy-${Date.now()}`);
    });

    const rawKey = await integrationsPage.createdKeyInput.inputValue();
    expect(rawKey).toMatch(/^sk_/);

    await integrationsPage.clickUntilOnPausedClock(
      integrationsPage.copyKeyButton,
      integrationsPage.copiedKeyButton,
    );

    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(
      rawKey,
    );
    await expect(integrationsPage.copiedKeyButton).toBeDisabled();
    await expect(integrationsPage.createdKeyInput).toBeFocused();
    await expect
      .poll(() =>
        integrationsPage.createdKeyInput.evaluate((input: HTMLInputElement) => [
          input.selectionStart,
          input.selectionEnd,
        ]),
      )
      .toEqual([0, rawKey.length]);

    await page.clock.runFor(1999);
    await expect(integrationsPage.copiedKeyButton).toBeDisabled();

    await page.clock.runFor(1);
    await expect(integrationsPage.copyKeyButton).toBeEnabled();
    await expect(integrationsPage.createdKeyInput).not.toBeFocused();
  });
});
