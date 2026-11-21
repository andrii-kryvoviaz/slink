import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const PARALLEL_REFRESHES = 16;

async function postJson(path: string, body: object): Promise<Response> {
  return fetch(`${API_URL}${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
}

test.describe('Concurrent token refresh', () => {
  test(
    'parallel refreshes with one refresh token let exactly one through',
    { tag: '@sc-1203' },
    async () => {
      const account = unique('refresh');
      await provisionUser(account);

      const login = await postJson('/api/auth/login', {
        username: account.username,
        password: account.password,
      });
      const session = await login.json();

      const responses = await Promise.all(
        Array.from({ length: PARALLEL_REFRESHES }, async () => {
          const response = await postJson('/api/auth/refresh', {
            refresh_token: session.refresh_token,
          });
          const body = await response.text();

          return { status: response.status, body };
        }),
      );

      const winners = responses.filter((response) => response.status === 200);
      const statuses = responses.map((response) => response.status).sort();

      expect(statuses).toEqual(
        [200, ...Array(PARALLEL_REFRESHES - 1).fill(400)].sort(),
      );

      const rotated = JSON.parse(winners[0].body);
      const next = await postJson('/api/auth/refresh', {
        refresh_token: rotated.refresh_token,
      });

      expect(next.status).toBe(200);
    },
  );
});
