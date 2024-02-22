import type { Handle } from '@sveltejs/kit';

import type { AbstractResource } from '@slink/api/AbstractResource';
import { ImageResource } from '@slink/api/Resources/ImageResource';

type Resource<T extends AbstractResource> = { new (baseUrl: string): T };

const RESOURCES: Record<string, Resource<any>> = {
  image: ImageResource,
};

type Resources = {
  [K in keyof typeof RESOURCES]: InstanceType<(typeof RESOURCES)[K]>;
};

type ApiClient = Resources & {
  use: (fetchFn: Function) => Resources;
};

class Client {
  private static _resources: Resources = {};

  public static create(baseUrl: string): ApiClient {
    this.initializeResources(baseUrl);

    return {
      ...this._resources,
      use: (fetchFn: Function) => {
        this.useFetch(fetchFn);
        return this._resources;
      },
    };
  }

  public static useFetch(fetchFn: Function) {
    for (const resource of Object.values(this._resources)) {
      resource.useFetch(fetchFn);
    }
  }

  private static initializeResources(baseUrl: string) {
    for (const resource in RESOURCES) {
      const resourceClass = RESOURCES[resource];
      this._resources[resource] = new resourceClass(baseUrl);
    }
  }
}

export const ApiClient = Client.create('/api');
export const setFetchHandle: Handle = async ({ event, resolve }) => {
  Client.useFetch(event.fetch);
  return resolve(event);
};
