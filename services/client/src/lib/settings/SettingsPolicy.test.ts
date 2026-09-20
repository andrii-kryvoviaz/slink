import { describe, expect, it } from 'vitest';

import {
  type ViewMode,
  type ViewModeSettingsKey,
  defaultSettings,
  defaultViewModes,
  supportedViewModes,
} from '@slink/lib/settings/Settings.enums';
import { settingsPolicy } from '@slink/lib/settings/SettingsPolicy';

const viewModeCases: {
  key: ViewModeSettingsKey;
  supported: ViewMode[];
  defaultMode: ViewMode;
  unsupported: ViewMode;
  kept: ViewMode;
}[] = [
  {
    key: 'userAdmin',
    supported: ['grid', 'list'],
    defaultMode: 'list',
    unsupported: 'table',
    kept: 'grid',
  },
  {
    key: 'history',
    supported: ['grid', 'list', 'table'],
    defaultMode: 'table',
    unsupported: 'tree',
    kept: 'list',
  },
  {
    key: 'explore',
    supported: ['grid', 'list'],
    defaultMode: 'grid',
    unsupported: 'table',
    kept: 'list',
  },
  {
    key: 'tags',
    supported: ['table', 'tree'],
    defaultMode: 'table',
    unsupported: 'grid',
    kept: 'tree',
  },
  {
    key: 'collections',
    supported: ['grid', 'table'],
    defaultMode: 'grid',
    unsupported: 'list',
    kept: 'table',
  },
  {
    key: 'bookmarks',
    supported: ['grid', 'list'],
    defaultMode: 'grid',
    unsupported: 'table',
    kept: 'list',
  },
];

const defaultViewMode = (key: ViewModeSettingsKey): unknown =>
  (defaultSettings[key] as { viewMode: unknown }).viewMode;

const decodedViewMode = (key: ViewModeSettingsKey, value: unknown): unknown =>
  (settingsPolicy.decode(key, JSON.stringify(value)) as { viewMode: unknown })
    .viewMode;

describe('settingsPolicy view-mode registry', () => {
  it('every registry key has an expectation', () => {
    expect(viewModeCases.map(({ key }) => key).sort()).toEqual(
      Object.keys(supportedViewModes).sort(),
    );
  });

  it.each(viewModeCases)(
    '$key: supports $supported and defaults to $defaultMode',
    ({ key, supported, defaultMode }) => {
      expect(supportedViewModes[key]).toEqual(supported);
      expect(defaultViewModes[key]).toBe(defaultMode);
      expect(defaultViewMode(key)).toBe(defaultMode);
    },
  );
});

describe('settingsPolicy view-mode fallback', () => {
  it.each(viewModeCases)(
    '$key: the unsupported viewMode $unsupported falls back to $defaultMode',
    ({ key, unsupported, defaultMode }) => {
      expect(decodedViewMode(key, { viewMode: unsupported })).toBe(defaultMode);
    },
  );

  it.each(viewModeCases)(
    '$key: bogus, numeric, null and absent viewMode fall back to $defaultMode',
    ({ key, defaultMode }) => {
      const inputs = [
        { viewMode: 'bogus' },
        { viewMode: 42 },
        { viewMode: null },
        {},
      ];

      for (const input of inputs) {
        expect(decodedViewMode(key, input)).toBe(defaultMode);
      }
    },
  );

  it('collections keeps pageSize and loadStrategy on view-mode fallback', () => {
    expect(settingsPolicy.decode('collections', '{"viewMode":"list"}')).toEqual(
      {
        viewMode: 'grid',
        pageSize: 12,
        loadStrategy: 'load_more',
      },
    );
  });

  it.each(viewModeCases)(
    '$key: the supported viewMode $kept is kept',
    ({ key, kept }) => {
      expect(decodedViewMode(key, { viewMode: kept })).toBe(kept);
    },
  );
});

describe('settingsPolicy bookmarks view mode', () => {
  it('decodes an unsupported viewMode to the default', () => {
    expect(settingsPolicy.decode('bookmarks', '{"viewMode":"table"}')).toEqual({
      viewMode: 'grid',
    });
  });

  it.each(['not json', 'null', '[]'])(
    'decodes a malformed value %s to the default',
    (raw) => {
      expect(settingsPolicy.decode('bookmarks', raw)).toEqual({
        viewMode: 'grid',
      });
    },
  );

  it('round-trips an encode then decode', () => {
    const encoded = settingsPolicy.encode({ viewMode: 'list' });

    expect(settingsPolicy.decode('bookmarks', encoded)).toEqual({
      viewMode: 'list',
    });
  });
});

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
