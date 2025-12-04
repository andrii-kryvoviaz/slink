export interface Identifiable {
  id: string;
}

export interface IndexedListOptions<T> {
  sortKey: (item: T) => number;
}

export class IndexedList<T extends Identifiable> {
  private _items: T[] = $state([]);
  private _index = new Map<string, number>();
  private readonly sortKey: (item: T) => number;

  constructor(options: IndexedListOptions<T>) {
    this.sortKey = options.sortKey;
  }

  get items(): T[] {
    return this._items;
  }

  get size(): number {
    return this._items.length;
  }

  get isEmpty(): boolean {
    return this._items.length === 0;
  }

  has(id: string): boolean {
    return this._index.has(id);
  }

  get(id: string): T | undefined {
    const idx = this._index.get(id);
    return idx !== undefined ? this._items[idx] : undefined;
  }

  set(items: T[]): void {
    this._items = items.sort((a, b) => this.sortKey(a) - this.sortKey(b));
    this.rebuildIndex();
  }

  insert(item: T): boolean {
    if (this._index.has(item.id)) return false;
    const insertIdx = this.findInsertIndex(this.sortKey(item));
    this._items = [
      ...this._items.slice(0, insertIdx),
      item,
      ...this._items.slice(insertIdx),
    ];
    this.rebuildIndex();
    return true;
  }

  update(id: string, updater: (item: T) => T): boolean {
    const idx = this._index.get(id);
    if (idx === undefined) return false;
    this._items[idx] = updater(this._items[idx]);
    return true;
  }

  patch(id: string, partial: Partial<T>): boolean {
    return this.update(id, (item) => ({ ...item, ...partial }));
  }

  remove(id: string): boolean {
    const idx = this._index.get(id);
    if (idx === undefined) return false;
    this._items = [...this._items.slice(0, idx), ...this._items.slice(idx + 1)];
    this.rebuildIndex();
    return true;
  }

  clear(): void {
    this._items = [];
    this._index.clear();
  }

  private rebuildIndex(): void {
    this._index.clear();
    this._items.forEach((item, i) => this._index.set(item.id, i));
  }

  private findInsertIndex(value: number): number {
    let low = 0;
    let high = this._items.length;
    while (low < high) {
      const mid = (low + high) >>> 1;
      if (this.sortKey(this._items[mid]) < value) low = mid + 1;
      else high = mid;
    }
    return low;
  }
}
