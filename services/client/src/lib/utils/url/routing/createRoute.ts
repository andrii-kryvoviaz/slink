import { page } from '$app/state';

export type RouteOptions = {
  absolute?: boolean;
};

export type PathResolver = (options?: RouteOptions) => string;

export type RouteBuilder<TParams extends unknown[] = []> = {
  (...args: [...TParams, RouteOptions?]): string;
  path: (...args: TParams) => PathResolver;
};

export const buildQueryString = (
  params: Record<string, string | number | boolean | undefined>,
): string => {
  const entries = Object.entries(params).filter(([, v]) => v !== undefined);
  if (entries.length === 0) return '';
  return '?' + entries.map(([k, v]) => `${k}=${v}`).join('&');
};

const applyOptions = (path: string, options?: RouteOptions): string => {
  return options?.absolute ? `${page.url.origin}${path}` : path;
};

export const createRoute = <TParams extends unknown[]>(
  pathBuilder: (...args: TParams) => string | PathResolver,
): RouteBuilder<TParams> => {
  const routeFn = (...args: [...TParams, RouteOptions?]) => {
    const lastArg = args[args.length - 1];
    const hasOptions =
      lastArg && typeof lastArg === 'object' && 'absolute' in lastArg;

    const params = (hasOptions
      ? args.slice(0, -1)
      : args) as unknown as TParams;
    const options = hasOptions ? (lastArg as RouteOptions) : undefined;

    const result = pathBuilder(...params);
    if (typeof result === 'function') {
      return result(options);
    }
    return applyOptions(result, options);
  };

  routeFn.path = (...args: TParams): PathResolver => {
    return (options?: RouteOptions) => {
      const result = pathBuilder(...args);
      if (typeof result === 'function') {
        return result(options);
      }
      return applyOptions(result, options);
    };
  };

  return routeFn as RouteBuilder<TParams>;
};
