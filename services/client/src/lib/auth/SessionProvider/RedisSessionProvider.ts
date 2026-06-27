import { type RedisClientType, createClient } from 'redis';

import type { SessionProviderInterface } from '@slink/lib/auth/SessionProvider/SessionProviderInterface';

const KEY_PREFIX = 'session:';

export class RedisSessionProvider implements SessionProviderInterface {
  private static _instance: RedisSessionProvider;

  private _connection: Promise<unknown> | null = null;

  private constructor(private readonly _redis: RedisClientType) {
    this._redis.on('error', (error) => {
      console.error('Redis connection error:', error);
    });
  }

  public static async createClient() {
    const redis = createClient();

    return (this._instance ??= new RedisSessionProvider(
      redis as RedisClientType,
    ));
  }

  private async _connect(): Promise<void> {
    if (this._redis.isReady) return;

    this._connection ??= this._redis.connect().catch((error) => {
      this._connection = null;
      throw error;
    });

    await this._connection;
  }

  private _key(sessionId: string): string {
    return KEY_PREFIX + sessionId;
  }

  async create(sessionId: string, ttl: number): Promise<void> {
    await this.set(sessionId, { data: { user: null } }, ttl);
  }

  async get<T>(sessionId: string): Promise<T | undefined> {
    await this._connect();

    const session = await this._redis.get(this._key(sessionId));

    return session ? JSON.parse(session) : undefined;
  }

  async set<T>(sessionId: string, data: T, ttl: number): Promise<void> {
    await this._connect();

    await this._redis.set(this._key(sessionId), JSON.stringify(data), {
      EX: ttl,
    });
  }

  async destroy(sessionId: string): Promise<void> {
    await this._connect();

    await this._redis.del(this._key(sessionId));
  }
}
