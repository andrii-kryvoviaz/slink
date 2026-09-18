import { beforeEach, describe, expect, it, vi } from 'vitest';

import { defaultSettings } from '@slink/lib/settings/Settings.enums';
import { UserSettings } from '@slink/lib/settings/UserSettings.svelte';

import { cookie } from '@slink/utils/http/cookie';

vi.mock('$app/environment', () => ({ browser: true }));
vi.mock('@slink/utils/http/cookie', () => ({
  cookie: {
    get: vi.fn(() => undefined),
    set: vi.fn(),
  },
}));

const objectKeys = [
  'sidebar',
  'navigation',
  'userAdmin',
  'table',
  'history',
  'explore',
  'tags',
  'share',
  'comment',
  'collections',
  'uploadOptions',
  'banners',
] as const;

describe('UserSettings', () => {
  beforeEach(() => {
    vi.mocked(cookie.set).mockClear();
  });

  it('an object setting seeds from the default', () => {
    const settings = new UserSettings({});

    expect(settings.sidebar).toEqual(defaultSettings.sidebar);
    expect(settings.table).toEqual(defaultSettings.table);
    expect(settings.explore).toEqual(defaultSettings.explore);
    expect(cookie.set).not.toHaveBeenCalled();
  });

  it('hydrate replaces the value without persisting', () => {
    const settings = new UserSettings({ explore: { viewMode: 'list' } });

    expect(settings.explore).toEqual({ viewMode: 'list' });
    expect(cookie.set).not.toHaveBeenCalled();
  });

  it('assigning persists through the policy', () => {
    const settings = new UserSettings({});

    settings.explore = { viewMode: 'list' };

    expect(settings.explore).toEqual({ viewMode: 'list' });
    expect(cookie.set).toHaveBeenCalledTimes(1);

    const [name, value] = vi.mocked(cookie.set).mock.calls[0]!;
    expect(name).toBe('settings.explore');
    expect(JSON.parse(value)).toEqual({ viewMode: 'list' });
  });

  it('each of the twelve keys round-trips through its accessor', () => {
    const settings = new UserSettings({});

    for (const key of objectKeys) {
      vi.mocked(cookie.set).mockClear();

      const value = { ...(defaultSettings[key] as object) };
      (settings as unknown as Record<string, unknown>)[key] = value;

      expect((settings as unknown as Record<string, unknown>)[key]).toEqual(
        value,
      );
      expect(cookie.set).toHaveBeenCalledTimes(1);
      expect(vi.mocked(cookie.set).mock.calls[0]![0]).toBe(`settings.${key}`);
    }
  });

  it('hydrate does not touch other keys', () => {
    const settings = new UserSettings({
      table: {
        ...(defaultSettings.table as object),
        users: { pageSize: 1, columnVisibility: {} },
      },
    });

    for (const key of objectKeys) {
      if (key === 'table') continue;
      expect((settings as unknown as Record<string, unknown>)[key]).toEqual(
        defaultSettings[key],
      );
    }
  });

  it('updateTable merges a partial and persists', () => {
    const settings = new UserSettings({});

    settings.updateTable({ tags: { pageSize: 99 } });

    expect(settings.table.tags.pageSize).toBe(99);
    expect(settings.table.tags.columnVisibility).toEqual(
      (defaultSettings.table as { tags: { columnVisibility: unknown } }).tags
        .columnVisibility,
    );
    expect(settings.table.users).toEqual(
      (defaultSettings.table as { users: unknown }).users,
    );
    expect(cookie.set).toHaveBeenCalledTimes(1);
    expect(vi.mocked(cookie.set).mock.calls[0]![0]).toBe('settings.table');
  });

  it('reset restores defaults', () => {
    const settings = new UserSettings({});

    settings.explore = { viewMode: 'list' };
    settings.sidebar = { expanded: false };
    vi.mocked(cookie.set).mockClear();

    settings.reset();

    expect(settings.explore).toEqual(defaultSettings.explore);
    expect(settings.sidebar).toEqual(defaultSettings.sidebar);
  });

  it('_apply does not clobber the field on hydration then persist', () => {
    const settings = new UserSettings({ sidebar: { expanded: false } });

    expect(settings.sidebar).toEqual({ expanded: false });

    settings.sidebar = { expanded: true };

    expect(settings.sidebar).toEqual({ expanded: true });
    expect(cookie.set).toHaveBeenCalledTimes(1);

    const [name, value] = vi.mocked(cookie.set).mock.calls[0]!;
    expect(name).toBe('settings.sidebar');
    expect(JSON.parse(value)).toEqual({ expanded: true });
  });
});
