import { injectable } from 'tsyringe';

import { CACHE } from '$lib/constants/app';

export interface CacheEntry<T> {
  data: T;
  cachedAt: number;
}

@injectable()
export class CacheService<T> {
  constructor(
    private readonly cacheKey: string,
    private readonly cacheDuration: number = CACHE.DURATION.ONE_HOUR,
  ) {}

  private isAvailable(): boolean {
    return typeof localStorage !== 'undefined';
  }

  get(): T | null {
    if (!this.isAvailable()) return null;

    try {
      const cached = localStorage.getItem(this.cacheKey);
      if (!cached) return null;

      const entry = JSON.parse(cached) as CacheEntry<T>;

      if (this.isExpired(entry)) {
        this.clear();
        return null;
      }

      return entry.data;
    } catch {
      this.clear();
      return null;
    }
  }

  set(data: T): void {
    if (!this.isAvailable()) return;

    try {
      const entry: CacheEntry<T> = {
        data,
        cachedAt: Date.now(),
      };
      localStorage.setItem(this.cacheKey, JSON.stringify(entry));
    } catch {
      // Ignore localStorage errors (e.g., quota exceeded, privacy mode)
    }
  }

  clear(): void {
    if (this.isAvailable()) {
      localStorage.removeItem(this.cacheKey);
    }
  }

  private isExpired(entry: CacheEntry<T>): boolean {
    return Date.now() - entry.cachedAt > this.cacheDuration;
  }
}
