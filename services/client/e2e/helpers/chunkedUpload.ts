import { type Response } from '@playwright/test';

export const isChunkedUploadCompletionResponse = (
  response: Response,
): boolean => {
  const request = response.request();

  return (
    /\/api\/upload\/chunked\/[^/]+\/\d+$/.test(request.url()) &&
    request.method() === 'PUT' &&
    request.headers()['x-upload-complete'] === 'true'
  );
};
