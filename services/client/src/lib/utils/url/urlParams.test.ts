import { describe, expect, it } from 'vitest';

import { UrlParamManager } from '@slink/utils/url/urlParams';

const manager = (search: string): UrlParamManager =>
  UrlParamManager.fromPageUrl(new URL(`http://localhost/p${search}`));

describe('UrlParamManager.searchFilter', () => {
  it('returns an empty filter for a missing, blank or whitespace search', () => {
    const blankSearches = ['', '?search=', '?search=%20%20%09'];

    for (const search of blankSearches) {
      expect(manager(search).searchFilter()).toEqual({});
      expect(
        manager(
          `${search}${search ? '&' : '?'}searchBy=description`,
        ).searchFilter(),
      ).toEqual({});
    }
  });

  it('trims the term and validates searchBy', () => {
    expect(
      manager('?search=%20cat%20&searchBy=hashtag').searchFilter(),
    ).toEqual({ searchTerm: 'cat', searchBy: 'hashtag' });

    expect(manager('?search=cat').searchFilter()).toEqual({
      searchTerm: 'cat',
      searchBy: 'user',
    });
  });

  it('falls back to user for a bogus searchBy', () => {
    expect(manager('?search=cat&searchBy=bogus').searchFilter()).toEqual({
      searchTerm: 'cat',
      searchBy: 'user',
    });

    expect(manager('?search=cat&searchBy=').searchFilter()).toEqual({
      searchTerm: 'cat',
      searchBy: 'user',
    });
  });

  it('picks a single deterministic term for a repeated search param', () => {
    expect(manager('?search=a&search=b').searchFilter()).toEqual({
      searchTerm: 'a',
      searchBy: 'user',
    });
  });
});
