import type { RequestOptions } from '@slink/api/Type/RequestOptions';

export type RequestMapper = (
  path: string,
  options?: RequestOptions,
) => RequestOptions | RequestInit;
