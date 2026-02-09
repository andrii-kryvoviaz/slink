import type { RequestEvent } from '@sveltejs/kit';

type RequestOptions = {
  method: string;
  headers: Headers;
  body?: globalThis.BodyInit;
  credentials: globalThis.RequestCredentials;
  duplex?: 'half';
};

export class ApiRequestBuilder {
  private requestEvent: RequestEvent;
  private baseUrl: string;
  private urlPrefix: string;

  constructor(requestEvent: RequestEvent, baseUrl: string, urlPrefix: string) {
    this.requestEvent = requestEvent;
    this.baseUrl = baseUrl;
    this.urlPrefix = urlPrefix;
  }

  public buildProxyUrl(): string {
    const { url } = this.requestEvent;
    const pathname = url.pathname.startsWith(this.urlPrefix)
      ? url.pathname
      : `${this.urlPrefix}${url.pathname}`;

    return `${this.baseUrl}${pathname}${url.search}`;
  }

  public async buildRequestOptions(
    accessToken?: string,
  ): Promise<RequestOptions> {
    const { request } = this.requestEvent;
    const { method } = request;

    const headers = new Headers({
      'Content-Type': request.headers.get('Content-Type') || 'application/json',
    });

    if (request.headers.has('Authorization')) {
      headers.set(
        'Authorization',
        request.headers.get('Authorization') as string,
      );
    } else if (accessToken) {
      headers.set('Authorization', `Bearer ${accessToken}`);
    }

    const body = await this.cloneRequestBody();

    return {
      method,
      headers,
      body,
      credentials: 'omit',
      duplex: 'half',
    };
  }

  private async cloneRequestBody(): Promise<globalThis.BodyInit | undefined> {
    const { request } = this.requestEvent;
    const contentType = request.headers.get('content-type');

    if (!contentType) {
      return undefined;
    }

    if (/^application\/json/i.test(contentType)) {
      return await request.clone().arrayBuffer();
    }

    if (/^multipart\/form-data/i.test(contentType)) {
      return request.clone().body as globalThis.BodyInit;
    }

    if (/^application\/x-www-form-urlencoded/i.test(contentType)) {
      return await request.clone().text();
    }

    return undefined;
  }
}
