import { type RequestEvent, isRedirect } from '@sveltejs/kit';
import { describe, expect, it, vi } from 'vitest';

import type { SsoProvider } from '@slink/api/Resources/SsoResource';

import { Gateway } from '@slink/lib/auth/Gateway';

const a: SsoProvider = { id: 'a-id', name: 'Provider A', slug: 'a-slug' };
const b: SsoProvider = { id: 'b-id', name: 'Provider B', slug: 'b-slug' };

function buildLocals(
  globalSettings: { user: { autoRedirectProviderId: string | null } } | null,
  getProviders: () => Promise<SsoProvider[]>,
): RequestEvent['locals'] {
  return {
    globalSettings,
    api: { sso: { getProviders } },
  } as unknown as RequestEvent['locals'];
}

async function expectRedirectTo(gateway: Gateway, location: string) {
  try {
    await gateway.redirect();
    expect.unreachable('redirect() should have thrown');
  } catch (error) {
    if (!isRedirect(error)) {
      throw error;
    }

    expect(error.status).toBe(302);
    expect(error.location).toBe(location);
  }
}

describe('Gateway', () => {
  it('U1: an unresolvable setting makes no API call', async () => {
    const rows = [{ user: { autoRedirectProviderId: null } }, null];

    for (const globalSettings of rows) {
      const getProviders = vi.fn(async () => [a, b]);
      const gateway = new Gateway(buildLocals(globalSettings, getProviders));

      await expect(gateway.provider()).resolves.toBeUndefined();
      await expect(gateway.url()).resolves.toBe('/profile/login');
      await expectRedirectTo(gateway, '/profile/login');

      expect(getProviders).not.toHaveBeenCalled();
    }
  });

  it('U2: a resolved provider is memoized to one fetch', async () => {
    const getProviders = vi.fn(async () => [a, b]);
    const gateway = new Gateway(
      buildLocals({ user: { autoRedirectProviderId: b.id } }, getProviders),
    );

    const [providers, provider, firstUrl, secondUrl] = await Promise.all([
      gateway.providers(),
      gateway.provider(),
      gateway.url(),
      gateway.url(),
    ]);

    expect(providers).toEqual([a, b]);
    expect(provider).toEqual(b);
    expect(firstUrl).toBe(`/profile/sso/login/${b.slug}`);
    expect(secondUrl).toBe(`/profile/sso/login/${b.slug}`);

    await expectRedirectTo(gateway, `/profile/sso/login/${b.slug}`);

    expect(getProviders).toHaveBeenCalledTimes(1);
  });

  it('U3: a stale id falls back to the login page', async () => {
    const getProviders = vi.fn(async () => [a, b]);
    const gateway = new Gateway(
      buildLocals(
        { user: { autoRedirectProviderId: crypto.randomUUID() } },
        getProviders,
      ),
    );

    await expect(gateway.provider()).resolves.toBeUndefined();
    await expect(gateway.url()).resolves.toBe('/profile/login');
    expect(getProviders).toHaveBeenCalledTimes(1);
  });

  it('U4: a provider fetch failure is graceful', async () => {
    const getProviders = vi.fn(async () => {
      throw new Error('network error');
    });
    const gateway = new Gateway(
      buildLocals({ user: { autoRedirectProviderId: a.id } }, getProviders),
    );

    await expect(gateway.providers()).resolves.toEqual([]);
    await expect(gateway.provider()).resolves.toBeUndefined();
    await expect(gateway.url()).resolves.toBe('/profile/login');
  });

  it('U5: providers() still lists providers when the setting is null', async () => {
    const getProviders = vi.fn(async () => [a, b]);
    const gateway = new Gateway(
      buildLocals({ user: { autoRedirectProviderId: null } }, getProviders),
    );

    await expect(gateway.providers()).resolves.toEqual([a, b]);
    await expect(gateway.provider()).resolves.toBeUndefined();
    await expect(gateway.url()).resolves.toBe('/profile/login');
    expect(getProviders).toHaveBeenCalledTimes(1);
  });
});
