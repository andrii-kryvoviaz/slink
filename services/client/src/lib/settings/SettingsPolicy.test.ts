import { describe, expect, it } from 'vitest';

import { settingsPolicy } from '@slink/lib/settings/SettingsPolicy';

describe('settingsPolicy', () => {
  it('names the explore key', () => {
    expect(settingsPolicy.name('explore')).toBe('settings.explore');
  });

  describe('decode', () => {
    const exploreDefault = { viewMode: 'grid' };

    it('decode returns the default for undefined', () => {
      expect(settingsPolicy.decode('explore', undefined)).toEqual(
        exploreDefault,
      );
    });

    it('decode returns the default for an empty string', () => {
      expect(settingsPolicy.decode('explore', '')).toEqual(exploreDefault);
    });

    it('decode returns the default for a not-json string', () => {
      expect(settingsPolicy.decode('explore', 'not-json')).toEqual(
        exploreDefault,
      );
    });

    it('decode merges an empty object onto the default', () => {
      expect(settingsPolicy.decode('explore', '{}')).toEqual(exploreDefault);
    });

    it('decode returns the default for a null literal', () => {
      expect(settingsPolicy.decode('explore', 'null')).toEqual(exploreDefault);
    });

    it('decode returns the default for an array literal', () => {
      expect(settingsPolicy.decode('explore', '[]')).toEqual(exploreDefault);
    });

    it('decode returns the default for a JSON string literal', () => {
      expect(settingsPolicy.decode('explore', JSON.stringify('list'))).toEqual(
        exploreDefault,
      );
    });

    it('decode keeps a supported viewMode', () => {
      expect(settingsPolicy.decode('explore', '{"viewMode":"list"}')).toEqual({
        viewMode: 'list',
      });
    });

    it('decode keeps a supported viewMode alongside an unknown field', () => {
      expect(
        settingsPolicy.decode('explore', '{"viewMode":"list","x":1}'),
      ).toEqual({ viewMode: 'list', x: 1 });
    });

    it('decode replaces an unsupported viewMode with the default', () => {
      expect(settingsPolicy.decode('explore', '{"viewMode":"table"}')).toEqual(
        exploreDefault,
      );
    });

    it('decode replaces a bogus viewMode with the default', () => {
      expect(settingsPolicy.decode('explore', '{"viewMode":"bogus"}')).toEqual(
        exploreDefault,
      );
    });

    it('decode returns the raw string for a string setting', () => {
      expect(settingsPolicy.decode('theme', 'dark')).toBe('dark');
    });

    it('decode never URI-decodes the raw value', () => {
      const result = settingsPolicy.decode(
        'explore',
        '%7B%22viewMode%22%3A%22list%22%7D',
      );

      expect(result).toEqual(exploreDefault);
    });

    it('encode then decode round-trips an object setting', () => {
      const encoded = settingsPolicy.encode({ viewMode: 'list' });

      expect(settingsPolicy.decode('explore', encoded)).toEqual({
        viewMode: 'list',
      });
    });
  });
});
