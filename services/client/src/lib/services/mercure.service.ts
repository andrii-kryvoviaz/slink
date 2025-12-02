type MessageHandler = (data: unknown) => void;
type ConnectionHandler = () => void;

export class MercureService {
  private static instance: MercureService | null = null;
  private connections = new Map<string, EventSource>();
  private pendingSubscriptions = new Map<string, Promise<() => void>>();

  private constructor() {}

  static getInstance(): MercureService {
    if (!this.instance) {
      this.instance = new MercureService();
    }
    return this.instance;
  }

  async subscribe(
    topic: string,
    onMessage: MessageHandler,
    onOpen?: ConnectionHandler,
    onError?: ConnectionHandler,
  ): Promise<() => void> {
    if (this.connections.has(topic)) {
      return () => this.unsubscribe(topic);
    }

    const pending = this.pendingSubscriptions.get(topic);
    if (pending) {
      return pending;
    }

    const subscriptionPromise = this.createSubscription(
      topic,
      onMessage,
      onOpen,
      onError,
    );
    this.pendingSubscriptions.set(topic, subscriptionPromise);

    try {
      return await subscriptionPromise;
    } finally {
      this.pendingSubscriptions.delete(topic);
    }
  }

  private createSubscription(
    topic: string,
    onMessage: MessageHandler,
    onOpen?: ConnectionHandler,
    onError?: ConnectionHandler,
  ): Promise<() => void> {
    const url = new URL('/sse', window.location.origin);
    url.searchParams.append('topic', topic);

    const eventSource = new EventSource(url.toString(), {
      withCredentials: true,
    });

    eventSource.onmessage = (event) => {
      try {
        onMessage(JSON.parse(event.data));
      } catch {
        onMessage(event.data);
      }
    };

    eventSource.onopen = () => onOpen?.();
    eventSource.onerror = () => onError?.();

    this.connections.set(topic, eventSource);

    return Promise.resolve(() => this.unsubscribe(topic));
  }

  unsubscribe(topic: string): void {
    const connection = this.connections.get(topic);
    if (connection) {
      connection.close();
      this.connections.delete(topic);
    }
  }

  unsubscribeAll(): void {
    this.connections.forEach((connection) => connection.close());
    this.connections.clear();
  }
}
