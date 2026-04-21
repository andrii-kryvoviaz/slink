import type { Reroute } from '@sveltejs/kit';

import { shareRoutes } from '$lib/routes/share';

type ShareRerouteRule = {
  match: RegExp;
  probe: (url: URL, match: RegExpMatchArray) => string;
};

export class ShareReroute {
  private static readonly rules: Map<string, ShareRerouteRule> = new Map([
    [
      'image',
      {
        match: /^\/image\/[0-9a-fA-F-]+\.[a-z0-9]+$/,
        probe: (url: URL) => `${url.origin}/api${url.pathname}${url.search}`,
      },
    ],
    [
      'collection',
      {
        match: /^\/collection\/([^/]+)$/,
        probe: (url: URL, m: RegExpMatchArray) =>
          `${url.origin}/api/collection/${m[1]}`,
      },
    ],
  ]);

  public static handle: Reroute = async ({ url, fetch }) => {
    const segment = url.pathname.split('/', 2)[1];
    if (!segment) {
      return;
    }

    const rule = ShareReroute.rules.get(segment);
    if (!rule) {
      return;
    }

    const match = url.pathname.match(rule.match);
    if (!match) {
      return;
    }

    try {
      const probe = await fetch(rule.probe(url, match));

      if (probe.status === 423) {
        const { shareId } = (await probe.json()) as { shareId?: string };
        if (!shareId) {
          return shareRoutes.unavailable;
        }
        return shareRoutes.locked(shareId);
      }

      if (probe.status === 404 || probe.status === 410) {
        return shareRoutes.unavailable;
      }
    } catch {
      return;
    }
  };
}
