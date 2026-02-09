import type { Transport } from '@sveltejs/kit';

import { Application } from '$lib/application';

import { API_CLIENT_BRAND } from '@slink/api/Client';

export const transport: Transport = {
  ApiClient: {
    encode: (value) =>
      value && typeof value === 'object' && API_CLIENT_BRAND in value && [0],
    decode: () => Application.api,
  },
};
