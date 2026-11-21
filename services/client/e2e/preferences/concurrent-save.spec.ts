import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const ROUNDS = 15;
const THEMES = ['nord', 'catppuccin'];
const LANGUAGES = ['de', 'en'];

async function patchPreferences(
  token: string,
  changes: Record<string, string>,
): Promise<number> {
  const response = await fetch(`${API_URL}/api/user/preferences`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify(changes),
  });

  return response.status;
}

async function readPreferences(
  token: string,
): Promise<Record<string, unknown>> {
  const response = await fetch(`${API_URL}/api/user/preferences`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  const body = await response.json();

  return body.data ?? body;
}

test.describe('Concurrent preference saves', () => {
  test('parallel saves of different preferences both survive', async () => {
    const api = await provisionUser(unique('prefsave'));

    for (let round = 0; round < ROUNDS; round++) {
      const theme = THEMES[round % THEMES.length];
      const language = LANGUAGES[round % LANGUAGES.length];

      const statuses = await Promise.all([
        patchPreferences(api.token, { 'display.theme': theme }),
        patchPreferences(api.token, { 'display.language': language }),
      ]);

      expect(statuses, `round ${round}`).toEqual([204, 204]);

      const preferences = await readPreferences(api.token);

      expect(preferences['display.theme'], `round ${round}`).toBe(theme);
      expect(preferences['display.language'], `round ${round}`).toBe(language);
    }
  });
});
