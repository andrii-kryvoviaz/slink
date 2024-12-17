import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

import { browser } from '$app/environment';
import { invalidateAll } from '$app/navigation';

import {
  BadRequestException, ForbiddenException, NotFoundException, UnauthorizedException, ValidationException
} from '@slink/api/Exceptions';
import { JsonMapper } from '@slink/api/Mapper/JsonMapper';
import { AnalyticsResource, AuthResource, ImageResource, SettingResource } from '@slink/api/Resources';
import { UserResource } from '@slink/api/Resources/UserResource';

const RESOURCES = {
  image: ImageResource,
  auth: AuthResource,
  user: UserResource,
  analytics: AnalyticsResource,
  setting: SettingResource,
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

type ApiClient = Resources & {
  use: (callable: FetchFunction) => Resources;
  on: (event: EventType, listener: EventListener) => void;
};

type EventType =
  | 'auth-refreshed'
  | 'unauthorized'
  | 'forbidden'
  | 'not-found'
  | 'bad-request'
  | 'validation'
  | 'error';

type Event<T> = {
  type: EventType;
  data?: T;
};

type EventListener = (event: Event<any>) => void;

export class Client {
  private static _resources: Resources = {} as Resources;
  private static _instance: Client;

  private _fetch: FetchFunction | null = null;
  private _mappers: Set<RequestMapper> = new Set([JsonMapper]);
  private _eventListeners: Map<EventType, EventListener[]> = new Map();

  private constructor(private readonly _basePath: string) {}

  public static create(basePath: string): ApiClient {
    const client = this._instance ?? new Client(basePath);
    this.initializeResources(client);

    return {
      ...this._resources,
      use: client.use.bind(client),
      on: client.on.bind(client),
    };
  }

  private static initializeResources(client: Client) {
    let ResourceType: ResourceType;

    for (ResourceType in RESOURCES) {
      const resourceClass: ResourceConstructor<any> = RESOURCES[ResourceType];
      this._resources[ResourceType] = new resourceClass(client);
    }
  }

  private generateQueryString(query: Record<string, string> | null = null) {
    if (!query) {
      return '';
    }

    const params = new URLSearchParams(query);

    return `?${params.toString()}`;
  }

  public async fetch(path: string, options?: RequestOptions) {
    if (!this._fetch) {
      this._fetch = fetch;
      console.warn(
        'API client is not initialized with fetch function, falling back to global fetch function. To utilize SSR, add `ApiClient.use(fetch)` to your `load` function.',
      );
    }

    for (const mapper of this._mappers) {
      options = mapper(path, options);
    }

    const url = [this._basePath, path].join('');

    const queryString = this.generateQueryString(options?.query);
    const response = await this._fetch(url + queryString, options);

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
      return {
        ...this.parseBody(responseBody),
        ...((options as RequestOptions)?.includeResponseHeaders
          ? { headers: response.headers }
          : {}),
      };
    }
  }

  private parseBody(body: any) {
    if (body.meta && body.data) {
      return body;
    }

    if (body.data && !Array.isArray(body.data)) {
      return body.data;
    }

    return body;
  }

  public use(callable: FetchFunction): Resources {
    this._fetch = callable;
    return Client._resources;
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

export const ApiClient = Client.create('/api');
