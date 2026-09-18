import { beforeEach, describe, expect, it, vi } from 'vitest';

import { createSearchFilterManager } from '@slink/lib/composables/useSearchFilterUrl';

import { replaceUrl } from '@slink/utils/navigation';

vi.mock('@slink/utils/navigation', () => ({
  replaceUrl: vi.fn(),
}));

const pageUrl = (path: string) => new URL(path, 'https://slink.test');

describe('UrlSearchFilterManager', () => {
  beforeEach(() => {
    vi.mocked(replaceUrl).mockClear();
  });

  it('reads a valid search filter', () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=cat&searchBy=user'),
    );

    expect(manager.read()).toEqual({ searchTerm: 'cat', searchBy: 'user' });
  });

  it('defaults the scope when missing', () => {
    const manager = createSearchFilterManager(pageUrl('/explore?search=cat'));

    expect(manager.read()).toEqual({ searchTerm: 'cat', searchBy: 'user' });
  });

  it('defaults the scope when invalid', () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=cat&searchBy=bogus'),
    );

    expect(manager.read()).toEqual({ searchTerm: 'cat', searchBy: 'user' });
  });

  it('treats a blank term as no search', () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=%20%20&searchBy=user'),
    );

    expect(manager.read()).toEqual({});
    expect(manager.hasFilterInUrl()).toBe(false);
  });

  it('reports whether a filter is present in the url', () => {
    expect(
      createSearchFilterManager(
        pageUrl('/explore?search=cat'),
      ).hasFilterInUrl(),
    ).toBe(true);
    expect(
      createSearchFilterManager(pageUrl('/explore')).hasFilterInUrl(),
    ).toBe(false);
  });

  it('writes a trimmed term and scope, navigating once', async () => {
    const manager = createSearchFilterManager(pageUrl('/explore'));

    await manager.updateUrl(' cat ', 'user');

    expect(replaceUrl).toHaveBeenCalledTimes(1);
    const [url, config] = vi.mocked(replaceUrl).mock.calls[0]!;
    expect(new URL(url, 'https://slink.test').searchParams.get('search')).toBe(
      'cat',
    );
    expect(
      new URL(url, 'https://slink.test').searchParams.get('searchBy'),
    ).toBe('user');
    expect(config).toEqual({ keepFocus: true, noScroll: true });
  });

  it('does not navigate when the trimmed term is unchanged', async () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=cat&searchBy=user'),
    );

    await manager.updateUrl('cat ', 'user');

    expect(replaceUrl).not.toHaveBeenCalled();
  });

  it('strips both params for a blank term', async () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=cat&searchBy=user'),
    );

    await manager.updateUrl('  ', 'user');

    expect(replaceUrl).toHaveBeenCalledTimes(1);
    const [url] = vi.mocked(replaceUrl).mock.calls[0]!;
    const params = new URL(url, 'https://slink.test').searchParams;
    expect(params.has('search')).toBe(false);
    expect(params.has('searchBy')).toBe(false);
  });

  it('preserves other params when writing', async () => {
    const manager = createSearchFilterManager(pageUrl('/explore?foo=1'));

    await manager.updateUrl('cat', 'user');

    const [url] = vi.mocked(replaceUrl).mock.calls[0]!;
    expect(new URL(url, 'https://slink.test').searchParams.get('foo')).toBe(
      '1',
    );
  });

  it('clears both params while preserving others', async () => {
    const manager = createSearchFilterManager(
      pageUrl('/explore?search=cat&searchBy=user&foo=1'),
    );

    await manager.clearUrl();

    expect(replaceUrl).toHaveBeenCalledTimes(1);
    const [url, config] = vi.mocked(replaceUrl).mock.calls[0]!;
    const params = new URL(url, 'https://slink.test').searchParams;
    expect(params.has('search')).toBe(false);
    expect(params.has('searchBy')).toBe(false);
    expect(params.get('foo')).toBe('1');
    expect(config).toEqual({ keepFocus: true, noScroll: true });
  });

  it('does not navigate clearing an already clean url', async () => {
    const manager = createSearchFilterManager(pageUrl('/explore'));

    await manager.clearUrl();

    expect(replaceUrl).not.toHaveBeenCalled();
  });
});
