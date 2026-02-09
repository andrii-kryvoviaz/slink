import { browser } from '$app/environment';
import { invalidateAll } from '$app/navigation';

import {
  BadRequestException,
  ForbiddenException,
  NotFoundException,
  PayloadTooLargeException,
  UnauthorizedException,
  ValidationException,
} from '@slink/api/Exceptions';
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
  TagResource,
} from '@slink/api/Resources';
import { ApiKeyResource } from '@slink/api/Resources/ApiKeyResource';
import { StorageResource } from '@slink/api/Resources/StorageResource';
import { UserResource } from '@slink/api/Resources/UserResource';
import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

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

type EventType =
  | 'auth-refreshed'
  | 'unauthorized'
  | 'forbidden'
  | 'not-found'
  | 'payload-too-large'
  | 'bad-request'
  | 'validation'
  | 'error';

type Event<T> = {
  type: EventType;
  data?: T;
};

type EventListener = (event: Event<unknown>) => void;

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

  private generateQueryString(query: Record<string, string> | null = null) {
    if (!query) {
      return '';
    }

    const params = new URLSearchParams(query);

    return `?${params.toString()}`;
  }

  public async fetch(path: string, options?: RequestOptions): Promise<unknown> {
    const fetchFn = this._fetch;

    for (const mapper of this._mappers) {
      options = mapper(path, options);
    }

    const url = [this._basePath, path].join('');

    const queryString = this.generateQueryString(
      options?.query as Record<string, string> | undefined,
    );
    const response = await fetchFn(url + queryString, options);

    if (browser && response.headers.has('x-auth-refreshed')) {
      const { handled } = this.emit({
        event: 'auth-refreshed',
      });

      if (!handled) {
        invalidateAll();
      }
    }

    if (response.status === 204) {
      return;
    }

    if (response.status === 401) {
      const { handled } = this.emit({
        event: 'unauthorized',
      });

      if (!handled) {
        throw new UnauthorizedException();
      }
    }

    if (response.status === 403) {
      const { handled } = this.emit({
        event: 'forbidden',
      });

      if (!handled) {
        throw new ForbiddenException();
      }
    }

    if (response.status === 404) {
      const { handled } = this.emit({
        event: 'not-found',
      });

      if (!handled) {
        throw new NotFoundException();
      }
    }

    if (response.status === 413) {
      const { handled } = this.emit({
        event: 'payload-too-large',
      });

      if (!handled) {
        throw new PayloadTooLargeException();
      }
    }

    const responseBody = await response.json();

    if (response.status === 400 && !responseBody.error?.violations) {
      const { handled } = this.emit({
        event: 'bad-request',
        data: responseBody.error,
      });

      if (!handled) {
        throw new BadRequestException(responseBody.error);
      }
    }

    if (response.status === 422 || responseBody.error?.violations) {
      const { handled } = this.emit({
        event: 'validation',
        data: responseBody.error,
      });

      if (!handled) {
        throw new ValidationException(responseBody.error);
      }
    }

    if (response.ok && response.status < 400) {
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

  private emit<T>({ event, data }: { event: EventType; data?: T }): {
    handled: boolean;
  } {
    const listeners = this._eventListeners.get(event);

    if (listeners) {
      listeners.forEach((listener) => listener({ type: event, data }));

      return {
        handled: true,
      };
    } else {
      return {
        handled: false,
      };
    }
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
