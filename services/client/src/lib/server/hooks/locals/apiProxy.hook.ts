import { env } from '$env/dynamic/private';
import type { Handle } from '@sveltejs/kit';

import { ApiProxy } from '@slink/api/ApiProxy';

import { defineHook } from '../define';

const injectApiHandling: Handle = ApiProxy({
  urlPrefix: '/api',
  baseUrl: env.API_URL || 'http://localhost:8080',
  registeredPaths: ['/api'],
});

export default defineHook({ handle: injectApiHandling });
