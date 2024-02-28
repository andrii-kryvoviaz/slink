import {
  BadRequestException,
  ValidationException,
} from '@slink/api/Exceptions';
import { JsonMapper } from '@slink/api/Mapper/JsonMapper';
import { AuthResource, ImageResource } from '@slink/api/Resources';
import { UserResource } from '@slink/api/Resources/UserResource';
import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

const RESOURCES = {
  image: ImageResource,
  auth: AuthResource,
  user: UserResource,
};

type ResourceType = keyof typeof RESOURCES;

type Resource<T extends ResourceType> = InstanceType<(typeof RESOURCES)[T]>;

type ResourceConstructor<T extends ResourceType> = new (
  client: Client
) => Resource<T>;

type Resources = {
  [K in ResourceType]: Resource<K>;
};

type FetchFunction = typeof fetch;

type ApiClient = Resources & {
  use: (callable: FetchFunction) => Resources;
};

export class Client {
  private static _resources: Resources = {} as Resources;
  private static _instance: Client;

  private _fetch: FetchFunction | null = null;
  private _mappers: Set<RequestMapper> = new Set([JsonMapper]);

  private constructor(private readonly _basePath: string) {}

  public static create(basePath: string): ApiClient {
    const client = this._instance ?? new Client(basePath);
    this.initializeResources(client);

    return {
      ...this._resources,
      use: client.use.bind(client),
    };
  }

  private static initializeResources(client: Client) {
    let ResourceType: ResourceType;

    for (ResourceType in RESOURCES) {
      const resourceClass: ResourceConstructor<any> = RESOURCES[ResourceType];
      this._resources[ResourceType] = new resourceClass(client);
    }
  }

  public async fetch(path: string, options?: RequestOptions | RequestInit) {
    if (!this._fetch) {
      this._fetch = fetch;
      console.warn(
        'API client is not initialized with fetch function, falling back to global fetch function. To utilize SSR, add `ApiClient.use(fetch)` to your `load` function.'
      );
    }

    for (const mapper of this._mappers) {
      options = mapper(path, options);
    }

    const url = [this._basePath, path].join('');

    const response = await this._fetch(url, options);

    if (response.status === 204) {
      return;
    }

    if ([401, 403, 404].includes(response.status)) {
      return;
    }

    const responseBody = await response.json();

    if (response.status === 400 && !responseBody.error?.violations) {
      throw new BadRequestException(responseBody.error, response.status);
    }

    if (response.status === 422 || responseBody.error?.violations) {
      throw new ValidationException(responseBody.error, response.status);
    }

    if (response.ok && response.status < 400) {
      return {
        ...(responseBody.data || responseBody),
        ...((options as RequestOptions)?.includeResponseHeaders
          ? { headers: response.headers }
          : {}),
      };
    }
  }

  public use(callable: FetchFunction): Resources {
    this._fetch = callable;
    return Client._resources;
  }
}

export const ApiClient = Client.create('/api');
