import { ImageResource } from './resources/ImageResource';

class Client {
  private _baseUrl: string;
  private static _instance: Client;

  private constructor(baseUrl: string) {
    this._baseUrl = baseUrl;

    // Initialize resources
    this.image = new ImageResource(this._baseUrl);
  }

  public static create(baseUrl: string) {
    if (!this._instance) {
      this._instance = new Client(baseUrl);
    }

    return this._instance;
  }

  public image: ImageResource;
}

export const ApiClient = Client.create('/api');
