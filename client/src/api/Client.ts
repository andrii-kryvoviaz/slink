import { type Handle, error } from '@sveltejs/kit';

import type { AbstractResource } from '@slink/api/AbstractResource';
import {
  BadRequestException,
  ForbiddenException,
  NotFoundException,
  ValidationException,
} from '@slink/api/Exceptions';
import { JsonMapper } from '@slink/api/Mapper/JsonMapper';
import { ImageResource } from '@slink/api/Resources/ImageResource';
import type { RequestMapper } from '@slink/api/Type/RequestMapper';
import type { RequestOptions } from '@slink/api/Type/RequestOptions';

type Resource<T extends AbstractResource> = { new (client: Client): T };

const RESOURCES: Record<string, Resource<any>> = {
  image: ImageResource,
};

type Resources = {
  [K in keyof typeof RESOURCES]: InstanceType<(typeof RESOURCES)[K]>;
};

type FetchFunction = typeof fetch;

type ApiClient = Resources & {
  use: (callable: FetchFunction) => Resources;
};

export class Client implements ApiClient {
  private static _resources: Resources = {};
  private static _instance: ApiClient;

  private _fetch: FetchFunction | null = null;
  private _mappers: Set<RequestMapper> = new Set([JsonMapper]);

  private constructor(private readonly _basePath: string) {}

  public static create(basePath: string): ApiClient {
    const client = this._instance ?? new Client(basePath);
    this.initializeResources(client as Client);

    return {
      ...this._resources,
      use: client.use.bind(client),
    };
  }

  private static initializeResources(client: Client) {
    for (const resource in RESOURCES) {
      const resourceClass = RESOURCES[resource];
      this._resources[resource] = new resourceClass(client);
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

    if (response.status === 403) {
      throw new ForbiddenException(response.status);
    }

    if (response.status === 404) {
      throw new NotFoundException(response.status);
    }

    const responseBody = await response.json();

    if (response.status === 400) {
      throw new BadRequestException(responseBody.error, response.status);
    }

    if (response.status === 422) {
      throw new ValidationException(responseBody.error, response.status);
    }

    if (response.ok && response.status < 400) {
      return responseBody?.data ?? responseBody;
    }
  }

  public use(callable: FetchFunction): Resources {
    this._fetch = callable;
    return Client._resources;
  }
}

export const ApiClient = Client.create('/api');

export const setFetchHandle: Handle = async ({ event, resolve }) => {
  ApiClient.use(event.fetch);
  return resolve(event);
};
