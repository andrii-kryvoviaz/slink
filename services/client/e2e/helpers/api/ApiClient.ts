import { HttpClient } from './HttpClient';
import { ContentApi } from './resources/ContentApi';
import { OAuthApi } from './resources/OAuthApi';
import { PreferencesApi } from './resources/PreferencesApi';
import { SettingsApi } from './resources/SettingsApi';
import { SharesApi } from './resources/SharesApi';
import { UsersApi } from './resources/UsersApi';

export class ApiClient {
  private static readonly _clients = new Map<string, Promise<ApiClient>>();

  readonly settings: SettingsApi;
  readonly users: UsersApi;
  readonly preferences: PreferencesApi;
  readonly content: ContentApi;
  readonly shares: SharesApi;
  readonly oauth: OAuthApi;

  private constructor(private http: HttpClient) {
    this.settings = new SettingsApi(http);
    this.users = new UsersApi(http);
    this.preferences = new PreferencesApi(http);
    this.content = new ContentApi(http);
    this.shares = new SharesApi(http);
    this.oauth = new OAuthApi(http);
  }

  get token() {
    return this.http.token;
  }

  async serverTime(): Promise<number> {
    return this.http.serverTime();
  }

  static createForUser(username: string, password: string): Promise<ApiClient> {
    const cached = ApiClient._clients.get(username);

    if (cached) {
      return cached;
    }

    const client = HttpClient.createForUser(username, password).then(
      (http) => new ApiClient(http),
    );
    ApiClient._clients.set(username, client);
    client.catch(() => ApiClient._clients.delete(username));

    return client;
  }
}
