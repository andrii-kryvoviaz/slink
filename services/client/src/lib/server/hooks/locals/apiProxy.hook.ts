import type { Handle } from '@sveltejs/kit/hooks';

import { API_URL } from '$app/env/private';

import { ApiProxy } from '@slink/api/ApiProxy';

import { defineHook } from '../define';

const injectApiHandling: Handle = ApiProxy({
  urlPrefix: '/api',
  baseUrl: API_URL || 'http://localhost:8080',
  registeredPaths: ['/api'],
});

export default defineHook({ handle: injectApiHandling });
