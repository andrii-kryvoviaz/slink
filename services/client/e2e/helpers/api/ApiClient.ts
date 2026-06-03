import { HttpClient } from './HttpClient';
import { ContentApi } from './resources/ContentApi';
import { PreferencesApi } from './resources/PreferencesApi';
import { SettingsApi } from './resources/SettingsApi';
import { SharesApi } from './resources/SharesApi';
import { UsersApi } from './resources/UsersApi';

export class ApiClient {
  readonly settings: SettingsApi;
  readonly users: UsersApi;
  readonly preferences: PreferencesApi;
  readonly content: ContentApi;
  readonly shares: SharesApi;

  private constructor(private http: HttpClient) {
    this.settings = new SettingsApi(http);
    this.users = new UsersApi(http);
    this.preferences = new PreferencesApi(http);
    this.content = new ContentApi(http);
    this.shares = new SharesApi(http);
  }

  get token() {
    return this.http.token;
  }

  static async create() {
    return new ApiClient(await HttpClient.create());
  }

  static async createForUser(username: string, password: string) {
    return new ApiClient(await HttpClient.createForUser(username, password));
  }
}
