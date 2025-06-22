import { json } from '@sveltejs/kit';

import type { RequestHandler } from '@sveltejs/kit';

export const GET: RequestHandler = async () => {
  // Return an empty response to satisfy Chrome DevTools
  // This prevents the "Not found" error in the console
  return json({});
};
