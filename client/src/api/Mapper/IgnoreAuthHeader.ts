import type { RequestMapper, RequestOptions } from '@slink/api/Type';

export const IgnoreAuthHeader: RequestMapper = (
  path,
  options
): RequestOptions | RequestInit => {
  const { ignoreAuth, ...rest } = options ?? {};
  if (!ignoreAuth) {
    return rest;
  }

  return {
    ...rest,
    headers: {
      ...rest?.headers,
      'x-ignore-auth': 'true',
    },
  };
};
