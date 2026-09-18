import { describe, expect, it } from 'vitest';

import { resolveSearchBy, searchByValues } from '@slink/utils/url/searchFilter';

describe('searchByValues', () => {
  it('lists the supported search scopes in order', () => {
    expect(searchByValues).toEqual(['user', 'description', 'hashtag']);
  });
});

describe('resolveSearchBy', () => {
  it('returns a listed value unchanged', () => {
    for (const value of searchByValues) {
      expect(resolveSearchBy(value)).toBe(value);
    }
  });

  it('falls back to user for unknown, empty and non-string input', () => {
    const invalidValues: unknown[] = [
      'bogus',
      '',
      'User',
      ' user',
      null,
      undefined,
      42,
      {},
      ['user'],
    ];

    for (const value of invalidValues) {
      expect(resolveSearchBy(value)).toBe('user');
    }
  });
});
