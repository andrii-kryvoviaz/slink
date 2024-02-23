import type {
  Invalidator,
  Readable,
  Subscriber,
  Unsubscriber,
} from 'svelte/store';

import { cookie } from '@slink/utils/http/cookie';

class CookieReadableStore<T> implements Readable<T> {
  constructor(
    private readonly cookieName: string,
    private readonly defaultValue?: T
  ) {}

  public subscribe(
    run: Subscriber<T>,
    invalidate?: Invalidator<T>
  ): Unsubscriber {
    run(
      cookie.get(this.cookieName, this.defaultValue as unknown as string) as T
    );

    const listener = (value: string) => run(value as unknown as T);
    let listenerId = cookie.subscribe(this.cookieName, listener);

    return () => {
      cookie.unsubscribe(this.cookieName, listenerId);
    };
  }
}

export function cookieReadable<T>(
  cookieName: string,
  defaultValue?: T
): Readable<T> {
  return new CookieReadableStore<T>(cookieName, defaultValue);
}
