export interface SessionProviderInterface {
  create(sessionId: string, ttl?: number | null): Promise<void>;

  get<T>(sessionId: string): Promise<T | undefined>;

  set<T>(sessionId: string, data: T, ttl?: number | null): Promise<void>;

  destroy(sessionId: string): Promise<void>;
}
