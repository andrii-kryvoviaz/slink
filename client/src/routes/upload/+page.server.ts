import type { PageServerLoad } from './$types';

import { parseUserAgentFromRequest } from '@slink/utils/http/userAgentParser';

export const load: PageServerLoad = async ({ request, locals }) => {
  const { user } = locals;

  return {
    user,
    ...parseUserAgentFromRequest(request),
  };
};
