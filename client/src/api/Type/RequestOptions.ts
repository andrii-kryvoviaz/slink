export type RequestOptions = RequestInit & {
  query?: Record<string, any>;
  json?: any;
  includeResponseHeaders?: boolean;
};
