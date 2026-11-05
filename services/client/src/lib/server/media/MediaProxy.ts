import { type RequestEvent, error } from '@sveltejs/kit';

import { MediaType } from '@slink/lib/enum/MediaType';

export class MediaProxy {
  private static readonly _upstreamPrefix = '/api/image';

  public static async forward({
    params,
    url,
    fetch,
  }: RequestEvent<{ path: string }>): Promise<Response> {
    const path = MediaProxy._resolvePath(params.path);
    const upstream = await fetch(
      `${MediaProxy._upstreamPrefix}/${path}${url.search}`,
      { redirect: 'manual' },
    );

    if (!upstream.ok) {
      return new Response(null, { status: upstream.status });
    }

    if (!MediaProxy._isMedia(upstream)) {
      error(404);
    }

    return MediaProxy._withCrossOriginAccess(upstream);
  }

  private static _resolvePath(path: string): string {
    if (path.includes('/')) {
      error(404);
    }

    return encodeURIComponent(path);
  }

  private static _isMedia(response: Response): boolean {
    const [type] = (response.headers.get('content-type') ?? '').split('/');

    return Object.values<string>(MediaType).includes(type);
  }

  private static _withCrossOriginAccess(upstream: Response): Response {
    const headers = new Headers(upstream.headers);
    headers.set('Access-Control-Allow-Origin', '*');
    headers.set('Cross-Origin-Resource-Policy', 'cross-origin');

    return new Response(upstream.body, {
      status: upstream.status,
      statusText: upstream.statusText,
      headers,
    });
  }
}
