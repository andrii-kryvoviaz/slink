import type { SessionProviderInterface } from '@slink/lib/auth/SessionProvider/SessionProviderInterface';
import { type RedisClientType, createClient } from 'redis';

export class RedisSessionProvider implements SessionProviderInterface {
  private static _instance: RedisSessionProvider;
  private defaultTtl: number = 60 * 60 * 24 * 7;
  private prefix: string = 'session:';
  private constructor(private _redis: RedisClientType) {}

  public static async createClient() {
    const redis = await createClient()
      .on('error', (err) => console.error('Redis Client Error', err))
      .connect();

    return (
      this._instance ??
      (this._instance = new RedisSessionProvider(redis as RedisClientType))
    );
  }

  async create(sessionId: string, ttl: number | null = null): Promise<void> {
    await this._redis.set(
      this.prefix + sessionId,
      JSON.stringify({ data: { user: null } })
    );
    await this._redis.expire(this.prefix + sessionId, ttl ?? this.defaultTtl);
  }

  async get<T>(sessionId: string): Promise<T | undefined> {
    const session = await this._redis.get(this.prefix + sessionId);

    if (!session) return;

    return JSON.parse(session);
  }

  async set<T>(
    sessionId: string,
    data: T,
    ttl: number | null = null
  ): Promise<void> {
    await this._redis.set(this.prefix + sessionId, JSON.stringify(data));
    await this._redis.expire(this.prefix + sessionId, ttl ?? this.defaultTtl);
  }

  async destroy(sessionId: string): Promise<void> {
    await this._redis.del(this.prefix + sessionId);
  }
}
