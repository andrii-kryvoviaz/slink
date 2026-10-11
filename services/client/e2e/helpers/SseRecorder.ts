import { type Page, expect } from '@playwright/test';

const OPEN_TIMEOUT = 3000;
const EVENT_TIMEOUT = 10000;

export type SseEvent = Record<string, any>;

type RecordingWindow = Window & { __sseRecordings?: Record<string, unknown[]> };

export class SseRecorder {
  constructor(private readonly _page: Page) {}

  async subscribe(topic: string): Promise<void> {
    await this._page.evaluate(
      ({ topic, openTimeout }) => {
        const w = window as RecordingWindow;
        w.__sseRecordings ??= {};
        const recording: unknown[] = [];
        w.__sseRecordings[topic] = recording;

        const url = new URL('/sse', window.location.origin);
        url.searchParams.append('topic', topic);

        const source = new EventSource(url.toString(), {
          withCredentials: true,
        });
        source.onmessage = (event) => {
          recording.push(JSON.parse(event.data));
        };

        return new Promise<void>((resolve, reject) => {
          const timer = setTimeout(() => {
            source.close();
            reject(
              new Error(
                `SSE stream for ${topic} did not open (readyState ${source.readyState})`,
              ),
            );
          }, openTimeout);
          source.onopen = () => {
            clearTimeout(timer);
            resolve();
          };
          source.onerror = () => {
            if (source.readyState === EventSource.OPEN) {
              return;
            }
            clearTimeout(timer);
            source.close();
            reject(
              new Error(
                `SSE stream for ${topic} errored before opening (readyState ${source.readyState})`,
              ),
            );
          };
        });
      },
      { topic, openTimeout: OPEN_TIMEOUT },
    );
  }

  async events<T = SseEvent>(topic: string): Promise<T[]> {
    return this._page.evaluate(
      (topic) => (window as RecordingWindow).__sseRecordings?.[topic] ?? [],
      topic,
    ) as Promise<T[]>;
  }

  async waitForEvent<T = SseEvent>(
    topic: string,
    predicate: (event: T) => boolean,
    timeout = EVENT_TIMEOUT,
  ): Promise<T> {
    await expect
      .poll(async () => (await this.events<T>(topic)).some(predicate), {
        timeout,
      })
      .toBe(true);

    return (await this.events<T>(topic)).find(predicate) as T;
  }
}
