import { type RedisClientType, createClient } from 'redis';

import type { SessionProviderInterface } from '@slink/lib/auth/SessionProvider/SessionProviderInterface';

export class RedisSessionProvider implements SessionProviderInterface {
  private static _instance: RedisSessionProvider;

  private connected: boolean = false;
  private prefix: string = 'session:';

  private constructor(private _redis: RedisClientType) {}

  public static async createClient() {
    const redis = createClient();

    return (
      this._instance ??
      (this._instance = new RedisSessionProvider(redis as RedisClientType))
    );
  }

  private async _connectIfNotConnected() {
    if (!this.connected) {
      this._redis
        .on('connect', () => {
          this.connected = true;
        })
        .on('error', () => {
          this.connected = false;
        });

      await this._redis.connect();
    }
  }

  async create(sessionId: string, ttl: number): Promise<void> {
    await this._connectIfNotConnected();

    await this._redis.set(
      this.prefix + sessionId,
      JSON.stringify({ data: { user: null } }),
    );
    await this._redis.expire(this.prefix + sessionId, ttl);
  }

  async get<T>(sessionId: string): Promise<T | undefined> {
    await this._connectIfNotConnected();

    const session = await this._redis.get(this.prefix + sessionId);

    if (!session) return;

    return JSON.parse(session);
  }

  async set<T>(sessionId: string, data: T, ttl: number): Promise<void> {
    await this._connectIfNotConnected();

    await this._redis.set(this.prefix + sessionId, JSON.stringify(data));
    await this._redis.expire(this.prefix + sessionId, ttl);
  }

  async destroy(sessionId: string): Promise<void> {
    await this._connectIfNotConnected();

    await this._redis.del(this.prefix + sessionId);
  }
}
