import { browser } from '$app/environment';
import { invalidateAll } from '$app/navigation';

import {
  BadRequestException,
  ForbiddenException,
  LockedException,
  NotFoundException,
  PayloadTooLargeException,
  UnauthorizedException,
  ValidationException,
} from '@slink/api/Exceptions';
import type { LockedExceptionPayload } from '@slink/api/Exceptions/LockedException';
import { HttpStatus } from '@slink/api/Http/HttpStatus';
import { JsonMapper } from '@slink/api/Mapper/JsonMapper';
import {
  AnalyticsResource,
  AuthResource,
  BookmarkResource,
  CollectionResource,
  CommentResource,
  ImageResource,
  NotificationResource,
  SettingResource,
  ShareResource,
  TagResource,
} from '@slink/api/Resources';
import { ApiKeyResource } from '@slink/api/Resources/ApiKeyResource';
import { OAuthResource } from '@slink/api/Resources/OAuthResource';
import { SsoResource } from '@slink/api/Resources/SsoResource';
import { StorageResource } from '@slink/api/Resources/StorageResource';
import { UserResource } from '@slink/api/Resources/UserResource';
import type { Violation } from '@slink/api/Response/Error/ViolationResponse';
import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

type ErrorPayload = {
  title?: string;
  message?: string;
  violations?: Violation[];
};

const errorOf = (body: unknown): ErrorPayload =>
  (body as { error?: ErrorPayload } | undefined)?.error ?? {};

const RESOURCES = {
  image: ImageResource,
  auth: AuthResource,
  user: UserResource,
  analytics: AnalyticsResource,
  setting: SettingResource,
  apiKey: ApiKeyResource,
  storage: StorageResource,
  tag: TagResource,
  comment: CommentResource,
  notification: NotificationResource,
  bookmark: BookmarkResource,
  collection: CollectionResource,
  share: ShareResource,
  sso: SsoResource,
  oauth: OAuthResource,
};

type ResourceType = keyof typeof RESOURCES;

type Resource<T extends ResourceType> = InstanceType<(typeof RESOURCES)[T]>;

type ResourceConstructor<T extends ResourceType> = new (
  client: Client,
) => Resource<T>;

type Resources = {
  [K in ResourceType]: Resource<K>;
};

type FetchFunction = typeof fetch;

export type ApiClientType = Resources & {
  on: (event: EventType, listener: EventListener) => void;
};

export type EventType =
  | 'auth-refreshed'
  | 'unauthorized'
  | 'forbidden'
  | 'not-found'
  | 'locked'
  | 'payload-too-large'
  | 'bad-request'
  | 'validation'
  | 'error';

type Event<T> = {
  type: EventType;
  data?: T;
};

type EventListener = (event: Event<unknown>) => void;

type EventContext = {
  url: string;
  method: string;
  body?: unknown;
};

type ResolvedException = { event: EventType; exception: Error };

type ExceptionResolver = (body: unknown) => ResolvedException;

const STATUS_EXCEPTIONS: Record<number, ExceptionResolver> = {
  [HttpStatus.Unauthorized]: () => ({
    event: 'unauthorized',
    exception: new UnauthorizedException(),
  }),
  [HttpStatus.Forbidden]: () => ({
    event: 'forbidden',
    exception: new ForbiddenException(),
  }),
  [HttpStatus.NotFound]: () => ({
    event: 'not-found',
    exception: new NotFoundException(),
  }),
  [HttpStatus.PayloadTooLarge]: () => ({
    event: 'payload-too-large',
    exception: new PayloadTooLargeException(),
  }),
  [HttpStatus.Locked]: (body) => ({
    event: 'locked',
    exception: new LockedException((body ?? {}) as LockedExceptionPayload),
  }),
  [HttpStatus.BadRequest]: (body) => {
    const error = errorOf(body);
    if (error.violations !== undefined) {
      return { event: 'validation', exception: new ValidationException(error) };
    }
    return { event: 'bad-request', exception: new BadRequestException(error) };
  },
  [HttpStatus.UnprocessableEntity]: (body) => ({
    event: 'validation',
    exception: new ValidationException(errorOf(body)),
  }),
};

export class Client {
  private _resources: Resources = {} as Resources;
  private _fetch: FetchFunction;
  private _mappers: Set<RequestMapper> = new Set([JsonMapper]);
  private _eventListeners: Map<EventType, EventListener[]> = new Map();

  constructor(
    private readonly _basePath: string,
    fetchFn: FetchFunction,
  ) {
    this._fetch = fetchFn;
    this.initializeResources();
  }

  private initializeResources() {
    let resourceType: ResourceType;

    for (resourceType in RESOURCES) {
      const resourceClass: ResourceConstructor<ResourceType> =
        RESOURCES[resourceType];
      (this._resources as Record<string, unknown>)[resourceType] =
        new resourceClass(this);
    }
  }

  public getResources(): Resources {
    return this._resources;
  }

  private generateQueryString(query: Record<string, unknown> | null = null) {
    if (!query) {
      return '';
    }

    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(query)) {
      if (value === undefined || value === null || value === '') continue;

      if (Array.isArray(value)) {
        if (value.length === 0) continue;
        const arrayKey = key.endsWith('[]') ? key : `${key}[]`;
        for (const item of value) {
          if (item === undefined || item === null || item === '') continue;
          params.append(arrayKey, String(item));
        }
        continue;
      }

      params.append(key, String(value));
    }

    const result = params.toString();
    return result ? `?${result}` : '';
  }

  public async fetch(path: string, options?: RequestOptions): Promise<unknown> {
    const fetchFn = this._fetch;

    for (const mapper of this._mappers) {
      options = mapper(path, options);
    }

    const url = [this._basePath, path].join('');

    const queryString = this.generateQueryString(options?.query ?? null);
    const response = await fetchFn(url + queryString, options);

    if (browser && response.headers.has('x-auth-refreshed')) {
      this.emit({ event: 'auth-refreshed' });
      invalidateAll();
    }

    if (response.status === HttpStatus.NoContent) {
      return;
    }

    const resolver = STATUS_EXCEPTIONS[response.status];
    const method = options?.method ?? 'GET';

    if (resolver !== undefined) {
      const body = await this.safeJson(response);
      const { event, exception } = resolver(body);
      this.emit<EventContext>({ event, data: { url, method, body } });
      throw exception;
    }

    const responseBody = await response.json();

    if (response.ok && response.status < HttpStatus.BadRequest) {
      const parsedBody = this.parseBody(responseBody);
      return {
        ...(typeof parsedBody === 'object' && parsedBody !== null
          ? parsedBody
          : {}),
        ...((options as RequestOptions)?.includeResponseHeaders
          ? { headers: response.headers }
          : {}),
      };
    }
  }

  private async safeJson(response: Response): Promise<unknown> {
    try {
      return await response.json();
    } catch {
      return undefined;
    }
  }

  private parseBody(body: unknown) {
    if (typeof body === 'object' && body !== null) {
      const obj = body as Record<string, unknown>;

      if (obj.meta && obj.data) {
        return body;
      }

      if (obj.data && !Array.isArray(obj.data)) {
        return obj.data;
      }
    }

    return body;
  }

  private emit<T>({ event, data }: { event: EventType; data?: T }): void {
    this._eventListeners
      .get(event)
      ?.forEach((listener) => listener({ type: event, data }));
  }

  public on(event: EventType, listener: EventListener) {
    let listeners = this._eventListeners.get(event);

    if (!listeners || !listeners.length) {
      listeners = [];
    }

    listeners.push(listener);

    this._eventListeners.set(event, listeners);
  }
}

export const API_CLIENT_BRAND = Symbol.for('slink:api-client');

function buildApiClientProxy(client: Client): ApiClientType {
  const resources = client.getResources();
  const proxy = {
    ...resources,
    on: client.on.bind(client),
  };
  Object.defineProperty(proxy, API_CLIENT_BRAND, { value: true });
  return proxy as ApiClientType;
}

export function createApiClient(fetchFn: FetchFunction): ApiClientType {
  const client = new Client('/api', fetchFn);
  return buildApiClientProxy(client);
}
