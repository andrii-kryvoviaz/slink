export type RequestOptions = globalThis.RequestInit & {
  query?: Record<string, unknown>;
  json?: unknown;
  includeResponseHeaders?: boolean;
};
