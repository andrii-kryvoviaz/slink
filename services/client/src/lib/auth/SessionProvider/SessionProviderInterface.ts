export interface SessionProviderInterface {
  create(sessionId: string, ttl: number): Promise<void>;

  get<T>(sessionId: string): Promise<T | undefined>;

  set<T>(sessionId: string, data: T, ttl: number): Promise<void>;

  destroy(sessionId: string): Promise<void>;
}
