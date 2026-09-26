import { isRedirect } from '@sveltejs/kit';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { goto } from '$app/navigation';

import { Application } from '@slink/lib/application/Application';

const env = vi.hoisted(() => ({ browser: false }));
const listeners = vi.hoisted(() => new Map<object, Map<string, () => void>>());
const deferred = vi.hoisted(() => ({ resolve: () => {} }));

vi.mock('$app/environment', () => ({
  get browser() {
    return env.browser;
  },
}));

vi.mock('$app/navigation', () => ({
  goto: vi.fn(
    () =>
      new Promise<void>((resolve) => {
        deferred.resolve = resolve;
      }),
  ),
}));

vi.mock('@slink/api/Client', () => ({
  createApiClient: vi.fn(() => {
    const client = {
      on: (event: string, listener: () => void) => {
        let clientListeners = listeners.get(client);
        if (!clientListeners) {
          clientListeners = new Map();
          listeners.set(client, clientListeners);
        }
        clientListeners.set(event, listener);
      },
    };
    return client;
  }),
}));

vi.mock('@slink/utils/ui/preloadIconSet', () => ({
  preloadIconSet: vi.fn(async () => {}),
}));

vi.mock('@slink/theme.icons', () => ({ themeIcons: {} }));
vi.mock('@slink/lib/utils/i18n/messages/api.language', () => ({}));
vi.mock('@slink/utils/string/stringExtensions', () => ({}));

const gotoMock = vi.mocked(goto);

function unauthorizedListenerOf(client: object): () => Promise<void> {
  const listener = listeners.get(client)?.get('unauthorized');
  if (!listener) {
    throw new Error('unauthorized listener was not registered');
  }
  return listener as unknown as () => Promise<void>;
}

async function initializeAndGetListener(gatewayUrl: string) {
  await Application.initialize(vi.fn() as unknown as typeof fetch, gatewayUrl);
  const client = Application.api;
  return unauthorizedListenerOf(client);
}

describe('Application', () => {
  beforeEach(async () => {
    deferred.resolve();
    await Promise.resolve();
    await Promise.resolve();
    gotoMock.mockClear();
    listeners.clear();
    env.browser = false;
  });

  it('SSR: an unauthorized event redirects to the gateway URL', async () => {
    env.browser = false;

    const listener = await initializeAndGetListener('/profile/sso/login/acme');

    try {
      listener();
      expect.unreachable('the listener should have thrown a redirect');
    } catch (error) {
      if (!isRedirect(error)) {
        throw error;
      }
      expect(error.status).toBe(302);
      expect(error.location).toBe('/profile/sso/login/acme');
    }
  });

  it('Client: an unauthorized event navigates to the gateway URL', async () => {
    env.browser = true;

    const listener = await initializeAndGetListener('/profile/sso/login/acme');

    listener();

    expect(gotoMock).toHaveBeenCalledTimes(1);
    expect(gotoMock).toHaveBeenCalledWith('/profile/sso/login/acme', {
      invalidateAll: true,
    });
  });

  it('Client dedupe: concurrent unauthorized events navigate once until settled', async () => {
    env.browser = true;

    const listener = await initializeAndGetListener('/profile/sso/login/acme');

    const first = listener();
    listener();

    expect(gotoMock).toHaveBeenCalledTimes(1);

    deferred.resolve();
    await first;

    listener();

    expect(gotoMock).toHaveBeenCalledTimes(2);
  });

  it('Rebinding: a second initialize keeps each client tied to its own gateway URL', async () => {
    env.browser = true;

    const firstListener = await initializeAndGetListener(
      '/profile/sso/login/acme',
    );

    const secondListener = await initializeAndGetListener(
      '/profile/sso/login/other',
    );

    secondListener();
    expect(gotoMock).toHaveBeenLastCalledWith('/profile/sso/login/other', {
      invalidateAll: true,
    });

    deferred.resolve();
    await Promise.resolve();
    await Promise.resolve();

    firstListener();
    expect(gotoMock).toHaveBeenLastCalledWith('/profile/sso/login/acme', {
      invalidateAll: true,
    });
  });
});
