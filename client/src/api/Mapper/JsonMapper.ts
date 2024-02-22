import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

export const JsonMapper: RequestMapper = (
  path,
  options
): RequestOptions | RequestInit => {
  const { json, ...rest } = options ?? {};

  if (!json) {
    return rest;
  }

  return {
    ...rest,
    body: JSON.stringify(json),
    headers: {
      ...rest.headers,
      'Content-Type': 'application/json',
    },
  };
};
