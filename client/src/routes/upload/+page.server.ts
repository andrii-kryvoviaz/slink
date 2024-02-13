import type { PageServerLoad } from './$types';
import { parseUserAgentFromRequest } from '@slink/utils/userAgentParser';

export const load: PageServerLoad = async ({ request }) => {
  return {
    ...parseUserAgentFromRequest(request),
  };
};
