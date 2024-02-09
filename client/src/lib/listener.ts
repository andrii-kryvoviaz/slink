export class Listener {
  private listeners: Map<number, (value: string) => void> = new Map();
  private nextListenerId: number = 0;

  public add(listener: (value: string) => void): number {
    const id = this.nextListenerId++;
    this.listeners.set(id, listener);
    return id;
  }

  public remove(id: number): void {
    this.listeners.delete(id);
  }

  public notify(value: string): void {
    this.listeners.forEach(listener => listener(value));
  }
}

export class ListenerAware {
  protected _listener: Listener = new Listener();

  public subscribe(listener: (value: string) => void): number {
    return this._listener.add(listener);
  }

  public unsubscribe(id: number): void {
    this._listener.remove(id);
  }
}