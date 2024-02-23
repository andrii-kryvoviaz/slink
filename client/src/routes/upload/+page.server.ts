import { parseUserAgentFromRequest } from '@slink/utils/http/userAgentParser';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request }) => {
  return {
    ...parseUserAgentFromRequest(request),
  };
};
