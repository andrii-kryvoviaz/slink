import { Application } from '$lib/application';

import type { ApiClientType } from './Client';

export const ApiClient: ApiClientType = new Proxy({} as ApiClientType, {
  get: (_, prop) => Reflect.get(Application.api, prop),
  has: (_, prop) => Reflect.has(Application.api, prop),
});
