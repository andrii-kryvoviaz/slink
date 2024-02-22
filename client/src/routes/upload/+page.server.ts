import { parseUserAgentFromRequest } from '@slink/utils/userAgentParser';

import type { PageServerLoad } from './$types';

export const load: PageServerLoad = async ({ request }) => {
  return {
    ...parseUserAgentFromRequest(request),
  };
};
