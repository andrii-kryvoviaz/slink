import { describe, expect, it } from 'vitest';

import { settingsPolicy } from '@slink/lib/settings/SettingsPolicy';

describe('settingsPolicy', () => {
  it('names the explore key', () => {
    expect(settingsPolicy.name('explore')).toBe('settings.explore');
  });
});
