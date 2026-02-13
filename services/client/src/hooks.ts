import type { Transport } from '@sveltejs/kit';

import { Application } from '$lib/application';

import { API_CLIENT_BRAND } from '@slink/api/Client';

import {
  USER_SETTINGS_BRAND,
  UserSettings,
} from '@slink/lib/settings/UserSettings.svelte';

let _clientSettings: UserSettings;

export const transport: Transport = {
  ApiClient: {
    encode: (value) =>
      value && typeof value === 'object' && API_CLIENT_BRAND in value && [0],
    decode: () => Application.api,
  },
  UserSettings: {
    encode: (value) =>
      value && typeof value === 'object' && USER_SETTINGS_BRAND in value && [0],
    decode: () => (_clientSettings ??= new UserSettings()),
  },
};
