export class Listener {
  private listeners: Map<string, Map<number, (value: string) => void>> = new Map();
  private nextListenerId: number = 0;

  public add(key: string, listener: (value: string) => void): number {
    if (!this.listeners.has(key)) {
      this.listeners.set(key, new Map());
    }

    const id = this.nextListenerId++;
    this.listeners.get(key)!.set(id, listener);
    return id;
  }

  public remove(key: string, id: number): void {
    this.listeners.get(key)?.delete(id);
  }

  public notify(key: string, value: string): void {
    this.listeners.get(key)?.forEach(listener => listener(value));
  }
}

export class ListenerAware {
  protected _listener: Listener = new Listener();

  public subscribe(key: string, listener: (value: string) => void): number {
    return this._listener.add(key, listener);
  }

  public unsubscribe(key: string, id: number): void {
    this._listener.remove(key, id);
  }
}