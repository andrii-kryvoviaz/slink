import { expect, test } from '../fixtures/auth.fixture';

const EXPECTED_TTL_SECONDS = 1209600;
const TOLERANCE_SECONDS = 3600;

test.describe('Session lifetime', { tag: '@anonymous' }, () => {
  const persistentCookies = ['refreshToken', 'sessionId'];

  test('auth cookies persist for the configured session lifetime', async ({
    loginPage,
    testUser,
  }) => {
    await loginPage.login(testUser.username, testUser.password);
    await loginPage.page.waitForURL(
      (url) => !url.pathname.startsWith('/profile/login'),
      { timeout: 30000 },
    );

    const cookies = await loginPage.page.context().cookies();

    for (const name of persistentCookies) {
      const cookie = cookies.find((candidate) => candidate.name === name);

      expect(cookie, `expected ${name} cookie to be set`).toBeDefined();
      expect(cookie!.expires).not.toBe(-1);
      expect(cookie!.httpOnly).toBe(true);

      const remaining = cookie!.expires - Date.now() / 1000;
      expect(Math.abs(remaining - EXPECTED_TTL_SECONDS)).toBeLessThanOrEqual(
        TOLERANCE_SECONDS,
      );
    }
  });
});
